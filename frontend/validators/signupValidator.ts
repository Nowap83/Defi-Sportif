import { z } from "zod";

export const signupSchema = z.object({
    nom: z
        .string()
        .min(3, { message: "Le nom d'un utilisateur doit contenir au moins 3 caractères" })
        .max(20, { message: "Le nom d'un utilisateur ne peut pas dépasser 20 caractères" }),

    prenom: z
        .string()
        .min(3, { message: "Le prenom d'un utilisateur doit contenir au moins 3 caractères" })
        .max(20, { message: "Le prenom d'un utilisateur ne peut pas dépasser 20 caractères" }),

    email: z
        .string()
        .email("Adresse e-mail invalide"),

    password: z
        .string()
        .min(6, { message: "Le mot de passe doit contenir au moins 6 caractères" })
        .max(50, { message: "Le mot de passe ne peut pas dépasser 50 caractères" })
        .regex(
            /(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/,
            {
                message: "Le mot de passe doit contenir une majuscule, une minuscule, un chiffre et un caractère spécial",
            }
        ),
});