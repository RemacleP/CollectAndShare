export interface FileValidatorOptions {
    maxSize?: number;          // Taille max en octets
    allowedTypes?: string[];   // Types MIME autorisés
}

export default {
    validate(file: File, options: FileValidatorOptions = {}): string | null {
        const { maxSize = 10 * 1024 * 1024, allowedTypes = [] } = options;

        if (allowedTypes.length && !allowedTypes.includes(file.type)) {
            return `Type de fichier non autorisé: ${file.name}`;
        }

        if (file.size > maxSize) {
            return `Taille du fichier trop grande: ${file.name}`;
        }

        return null; // Pas d'erreur
    },
};
