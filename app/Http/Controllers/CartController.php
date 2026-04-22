<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Element;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $items = CartItem::where('user_id', Auth::id())
            ->with(['element.collection.clubUser.user', 'element.images'])
            ->get();

        $grouped = $items->groupBy(function($item) {
            // On groupe par l'ID de l'utilisateur proprio de la collection via le pivot
            return $item->element->collection->clubUser->user_id;
        })->map(function($groupItems) {
            // On récupère les infos du vendeur
            $seller = $groupItems->first()->element->collection->clubUser->user;

            return [
                'seller' => [
                    'id' => $seller->id,
                    'username' => $seller->username,
                ],
                'items' => $groupItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'element' => [
                            'id' => $item->element->id,
                            'label' => $item->element->label,
                            'price' => $item->element->price,
                            'image' => $item->element->images->first()?->path ?? $item->element->image,
                        ]
                    ];
                }),
                'total_seller' => $groupItems->sum(fn($i) => $i->quantity * $i->element->price)
            ];
        })->values();

        return Inertia::render('Cart/index', [
            'cart' => [
                'groups' => $grouped,
                'total_price' => $items->sum(fn($i) => $i->quantity * $i->element->price),
                'total_items' => $items->sum('quantity')
            ]
        ]);
    }

    public function store(Request $request, Element $element)
    {
        // On remonte la chaîne : Element -> Collection -> ClubUser -> UserID
        if ($element->collection->clubUser->user_id === Auth::id()) {
            return back()->with('error', 'C\'est votre objet !');
        }

        CartItem::updateOrCreate(
            ['user_id' => Auth::id(), 'element_id' => $element->id],
            ['quantity' => \DB::raw('quantity + ' . ($request->quantity ?? 1))]
        );

        return back()->with('success', 'Ajouté au panier !');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $cartItem->update(['quantity' => $request->quantity]);
        return back();
    }

    public function destroy(CartItem $cartItem)
    {
        $cartItem->delete();
        return back();
    }
}
