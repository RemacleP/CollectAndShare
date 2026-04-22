<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Element;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class PaymentController extends Controller
{
    public function checkout()
    {
        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $cartItems = CartItem::where('user_id', Auth::id())->with('element')->get();

            $lineItems = [];
            foreach ($cartItems as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => ['name' => $item->element->label],
                        'unit_amount' => (int) (round($item->element->price, 2) * 100),
                    ],
                    'quantity' => $item->quantity,
                ];
            }

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cart.index'),
            ]);

            // C'EST ICI QUE ÇA CHANGE : on renvoie l'URL directe
            return response()->json(['url' => $session->url]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function prepareInvoiceSession($cartItems)
    {
        $items = $cartItems->map(fn($i) => [
            'label' => $i->element->label,
            'price' => (float)$i->element->price,
            'quantity' => $i->quantity,
            'total' => (float)($i->quantity * $i->element->price)
        ])->toArray();

        session(['invoice_data' => [
            'items' => $items,
            'total' => collect($items)->sum('total'),
            'user' => Auth::user(),
            'date' => now()->format('d/m/Y'),
        ]]);
    }

    public function success(Request $request)
    {
        $userId = Auth::id();

        // 1. On récupère les items avec les relations
        $cartItems = CartItem::where('user_id', $userId)
            ->with('element')
            ->get();

        if ($cartItems->isEmpty()) {
            return Inertia::render('Payment/Success');
        }

        try {
            DB::beginTransaction();

            // --- NOUVEAU : CRÉATION DE LA COMMANDE PRINCIPALE ---
            $order = Order::create([
                'user_id'           => $userId,
                'stripe_session_id' => $request->get('session_id'),
                'total_amount'      => $cartItems->sum(fn($item) => $item->element->price * $item->quantity),
                'status'            => 'paid',
            ]);

            foreach ($cartItems as $item) {
                $element = Element::find($item->element_id);

                if ($element) {
                    // --- NOUVEAU : ENREGISTREMENT DE CHAQUE LIGNE DE COMMANDE (PRIX FIGÉ) ---
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'element_id' => $element->id,
                        'label'      => $element->label, // On garde le nom en dur
                        'quantity'   => $item->quantity,
                        'price'      => $element->price,  // On garde le prix au moment de l'achat
                    ]);

                    // Mise à jour du stock
                    $element->decrement('quantity', $item->quantity);

                    Log::info("Stock mis à jour pour #{$element->id}");
                }
            }

            // Garder ta logique de facture session si tu en as besoin immédiatement
            $this->prepareInvoiceSession($cartItems);

            // RÉINITIALISATION : On vide le panier
            CartItem::where('user_id', $userId)->delete();

            DB::commit();
            Log::info("Commande #{$order->id} enregistrée et panier vidé.");

            // On peut passer l'ID de la commande à la vue pour afficher un numéro de commande
            return Inertia::render('Payment/Success', [
                'orderId' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur critique success paiement: " . $e->getMessage());
            return redirect()->route('cart.index')->with('error', 'Erreur lors de la validation.');
        }
    }

    public function downloadInvoice(Order $order)
    {
        // 1. SÉCURITÉ : On vérifie que la commande appartient bien à l'utilisateur connecté
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas l\'autorisation de voir cette facture.');
        }

        // 2. RÉCUPÉRATION : On charge les items associés à cette commande
        // On utilise les données figées en base (label, price)
        $items = $order->items->map(function ($item) {
            return [
                'label'    => $item->label,
                'price'    => $item->price,
                'quantity' => $item->quantity,
                'total'    => $item->price * $item->quantity,
            ];
        });

        // 3. PRÉPARATION : On formate les données pour la vue PDF
        $data = [
            'order_id' => $order->id,
            'items'    => $items,
            'total'    => $order->total_amount,
            'user'     => [
                'firstname' => Auth::user()->firstname,
                'lastname'  => Auth::user()->lastname,
                'email'     => Auth::user()->email,
            ],
            'date'     => $order->created_at->format('d/m/Y'),
        ];

        // 4. GÉNÉRATION : Utilisation de DomPDF avec ta vue existante
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', $data);

        return $pdf->download("facture-commande-{$order->id}.pdf");
    }

    public function history()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items') // Récupère les lignes de chaque commande
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders/History', [
            'orders' => $orders
        ]);
    }



}
