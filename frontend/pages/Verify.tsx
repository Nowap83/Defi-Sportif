import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import axios from "axios";
import { toast } from "react-hot-toast";

const Verify = () => {
  const { token } = useParams<{ token: string }>();
  const [loading, setLoading] = useState(true);
  const [verified, setVerified] = useState(false);
  const [message, setMessage] = useState("");

  useEffect(() => {
    const verifyAccount = async () => {
      if (!token) {
        setMessage("Token de vérification manquant.");
        setLoading(false);
        toast.error("Token de vérification manquant.");
        return;
      }

      try {
        const res = await axios.get(`http://localhost:8000/auth/verify-mail/${token}`);

        if (res.data?.success) {
          setVerified(true);
          setMessage(res.data.message);
          toast.success(res.data.message);
        } else {
          setVerified(false);
          setMessage(res.data.message || "Vérification échouée.");
          toast.error(res.data.message || "Erreur de vérification.");
        }
      } catch (err: any) {
        const msg = err.response?.data?.message || "Une erreur est survenue lors de la vérification.";
        setVerified(false);
        setMessage(msg);
        toast.error(msg);
      } finally {
        setLoading(false);
      }
    };

    verifyAccount();
  }, [token]);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-gray-50">
        <p className="text-gray-600 text-lg">Vérification en cours...</p>
      </div>
    );
  }

  return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-gray-50 px-4">
      <div className="bg-white shadow-lg rounded-2xl p-10 text-center max-w-md">
        {verified ? (
          <>
            <h1 className="text-3xl font-bold text-[#daf020] mb-4">
              ✅ Compte activé !
            </h1>
            <p className="text-gray-700 mb-6">
              Bienvenue dans la communauté{" "}
              <span className="font-semibold">AgoraFit</span> 🎉 <br />
              {message} <br />
              Vous êtes prêt à relever vos premiers défis sportifs 💪
            </p>
            <Link
              to="/login"
              className="px-6 py-3 rounded-2xl font-semibold transition-all duration-300 bg-[#daf020] text-black hover:bg-black hover:text-[#daf020]"
            >
              Se connecter
            </Link>
          </>
        ) : (
          <>
            <h1 className="text-3xl font-bold text-red-500 mb-4">
              ❌ Vérification échouée
            </h1>
            <p className="text-gray-700 mb-6">{message}</p>
            <Link
              to="/register"
              className="px-6 py-3 rounded-2xl font-semibold transition-all duration-300 bg-red-500 text-white hover:bg-black hover:text-white"
            >
              S'inscrire
            </Link>
          </>
        )}
      </div>
    </div>
  );
};

export default Verify;