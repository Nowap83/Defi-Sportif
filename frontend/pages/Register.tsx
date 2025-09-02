import React, { useState } from "react"
import type { ChangeEvent, FormEvent } from "react"
import { Link, useNavigate } from "react-router-dom"
import { toast } from "react-hot-toast"
import { signupSchema } from "../validators/signupValidator"
import { z } from "zod"
import axios from "axios"

// Définir l'interface pour ton formulaire
interface RegisterFormData {
  nom: string;
  prenom: string;
  email: string;
  password: string;
  confirmPassword: string;
}

const Register: React.FC = () => {
  const navigate = useNavigate();

  const [formData, setFormData] = useState<RegisterFormData>({
    nom: "",
    prenom: "",
    email: "",
    password: "",
    confirmPassword: "",
  });

  const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();


    if (formData.password !== formData.confirmPassword) { // verif du password
      toast.error("Les mots de passe ne correspondent pas.");
      return;
    }


    try {
      signupSchema.parse(formData); // la verif Zod

      const response = await axios.post(
        "http://91.168.22.101/api/auth/register",
        {
          nom: formData.nom,
          prenom: formData.prenom,
          email: formData.email,
          password: formData.password,
        },
        {
          headers: {
            "Content-Type": "application/json",
          },
        }
      );



      const data = response.data;

      if ((response.status === 200 || response.status === 201) && data?.success) {
        toast.success(data?.message || "Inscription réussie, veuillez valider le mail");
        setFormData({ nom: "", prenom: "", email: "", password: "", confirmPassword: "" });
        navigate("/login");
      }
    } catch (error) {
      if (error instanceof z.ZodError) {
        error.issues.forEach((err) => toast.error(err.message));

      } else if (axios.isAxiosError(error)) {
        const message = error.response?.data?.message || "Erreur côté serveur";
        toast.error(message);

      } else {
        toast.error("Une erreur s'est produite.");
      }
    }
  };

  return (
    <div className="hero min-h-screen bg-base-200">
      <div className="hero-content flex-col lg:flex-row-reverse">
        <div className="text-center lg:text-left">
          <h1 className="text-5xl font-bold">Inscription</h1>
          <p className="py-6">
            Créez votre compte pour accéder à toutes les fonctionnalités.
          </p>
        </div>
        <div className="card flex-shrink-0 w-full max-w-sm shadow-2xl bg-base-100">
          <form className="card-body" onSubmit={handleSubmit}>
            <div className="form-control">
              <label className="label">
                <span className="label-text">Nom</span>
              </label>
              <input
                type="text"
                name="nom"
                placeholder="Votre nom"
                className="input input-bordered"
                value={formData.nom}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-control">
              <label className="label">
                <span className="label-text">Prénom</span>
              </label>
              <input
                type="text"
                name="prenom"
                placeholder="Votre prénom"
                className="input input-bordered"
                value={formData.prenom}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-control">
              <label className="label">
                <span className="label-text">Email</span>
              </label>
              <input
                type="email"
                name="email"
                placeholder="email@exemple.com"
                className="input input-bordered"
                value={formData.email}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-control">
              <label className="label">
                <span className="label-text">Mot de passe</span>
              </label>
              <input
                type="password"
                name="password"
                placeholder="••••••••"
                className="input input-bordered"
                value={formData.password}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-control">
              <label className="label">
                <span className="label-text">Confirmez le mot de passe</span>
              </label>
              <input
                type="password"
                name="confirmPassword"
                placeholder="••••••••"
                className="input input-bordered"
                value={formData.confirmPassword}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-control mt-6">
              <button type="submit" className="btn btn-primary">
                S'inscrire
              </button>
            </div>

            <div className="text-center mt-4">
              <p>
                Déjà un compte?{" "}
                <Link to="/login" className="link link-primary">
                  Connectez-vous
                </Link>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default Register;
