<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class LegalServices
{
    /**
     * Le chemin vers le fichier JSON.
     * On utilise resource_path si le fichier fait partie du code source,
     * ou storage_path si c'est un contenu dynamique (mieux pour l'édition).
     */
    private string $path;

    public function __construct()
    {
        $this->path = storage_path('app/private/MentionsLegales.json');
    }

    /**
     * Récupère le contenu du JSON ou la structure par défaut.
     */
    public function getLegalContent(): array
    {
        if (!File::exists($this->path)) {
            $this->updateLegalContent($this->getDefaultStructure());
        }

        $content = File::get($this->path);
        return json_decode($content, true) ?? $this->getDefaultStructure();
    }

    /**
     * Sauvegarde les modifications dans le fichier JSON.
     */
    public function updateLegalContent(array $data): void
    {
        File::put($this->path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Structure initiale si le fichier n'existe pas.
     */
    private function getDefaultStructure(): array
    {
        return [
            "mentions_legales" => [
                "titre" => "Mentions légales",
                "informations_association" => [
                    "nom_association" => "",
                    "statut_juridique" => "",
                    "adresse" => "",
                    "numero_entreprise" => "",
                    "telephone" => "",
                    "email" => "",
                    "site_web" => ""
                ],
                "directeur_publication" => ["contact" => ""],
                "hebergeur" => ["nom" => "", "adresse" => ""],
                "propriete_intellectuelle" => "",
                "limitation_responsabilite" => "",
                "ia_act" => ["description" => "", "points" => []]
            ],
            "protection_donnees" => [
                "titre" => "Protection des données",
                "introduction" => "",
                "collecte_donnees" => ["inscription" => [], "utilisation_plateforme" => []],
                "finalites" => [],
                "base_legale" => [],
                "duree_conservation" => [
                    "compte_utilisateur" => "",
                    "donnees_connexion" => "",
                    "obligations_legales" => ""
                ],
                "partage_donnees" => ["partenaires" => []],
                "droits_utilisateurs" => [],
                "securite" => []
            ],
            "cookies" => [
                "titre" => "Politique Cookies",
                "introduction" => "",
                "types" => ["necessaires" => [], "mesure_audience" => [], "reseaux_sociaux" => [], "tiers" => []],
                "consentement" => ["options" => []],
                "duree_vie" => ["essentiels" => "", "analytiques" => ""]
            ],
            "version" => [
                "titre" => "Mise à jour",
                "derniere_mise_a_jour" => date('d/m/Y'),
                "note" => ""
            ]
        ];
    }
}
