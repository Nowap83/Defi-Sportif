import React, { useState, useContext } from 'react'
import type { ChangeEvent, FormEvent } from 'react'
import { useNavigate } from 'react-router-dom'
import { toast } from 'react-hot-toast'
import { AuthContext } from '../contexts/auth.context'
import axios, { AxiosError } from 'axios'
import type { User } from "../contexts/auth.context";


interface LoginFormData {
    email: string
    password: string
}

const Login: React.FC = () => {
    const navigate = useNavigate()
    const { setToken, setUser } = useContext(AuthContext)
    const [isLoading, setIsLoading] = useState<boolean>(false)

    const [formData, setFormData] = useState<LoginFormData>({
        email: "",
        password: ""
    })

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault()
        setIsLoading(true)

        try {
            const res = await axios.post<{ token: string }>("http://localhost:8000/auth/login", formData);

            const { token } = res.data;
            setToken(token);
            localStorage.setItem("token", token);

            const userRes = await axios.get<User>("http://localhost:8000/user/me", {
                headers: { Authorization: `Bearer ${token}` }
            });

            setUser(userRes.data);
            localStorage.setItem("user", JSON.stringify(userRes.data));


            toast.success("Connexion réussie")
            navigate("/profil")
        } catch (err) {
            const error = err as AxiosError<any>;

            if (error.response?.data?.message) {
                toast.error(error.response.data.message);
            } else {
                toast.error(error.message || "Connexion échouée");
            }
        } finally {
            setIsLoading(false)
        }

    }

    const handleChange = (e: ChangeEvent<HTMLInputElement>) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        })
    }

    return (
        <>
            <div className="hero min-h-screen bg-base-200">
                <div className="hero-content flex-col lg:flex-row-reverse">
                    <div className="text-center lg:text-left">
                        <h1 className="text-5xl font-bold">Connexion</h1>
                        <p className="py-6">Connectez-vous pour accéder à votre compte.</p>
                    </div>

                    <form
                        onSubmit={handleSubmit}
                        className="card flex-shrink-0 w-full max-w-sm shadow-2xl bg-base-100"
                    >
                        <div className="card-body">
                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">Email</span>
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    required
                                    value={formData.email}
                                    onChange={handleChange}
                                    placeholder="email@exemple.com"
                                    className="input input-bordered"
                                />
                            </div>

                            <div className="form-control">
                                <label className="label">
                                    <span className="label-text">Mot de passe</span>
                                </label>
                                <input
                                    type="password"
                                    name="password"
                                    required
                                    value={formData.password}
                                    onChange={handleChange}
                                    placeholder="••••••••"
                                    className="input input-bordered"
                                />
                                <label className="label">
                                    <a href="#" className="label-text-alt link link-hover">Mot de passe oublié?</a>
                                </label>
                            </div>

                            <div className="form-control mt-6">
                                <button
                                    type="submit"
                                    className="btn btn-primary"
                                    disabled={isLoading}
                                >
                                    {isLoading ? (
                                        <span className="loading loading-spinner"></span>
                                    ) : (
                                        "Se connecter"
                                    )}
                                </button>
                            </div>

                            <div className="text-center mt-4">
                                <p>
                                    Pas encore de compte?
                                    <a href="/register" className="link link-primary ml-1">
                                        Inscrivez-vous
                                    </a>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </>
    )
}

export default Login
