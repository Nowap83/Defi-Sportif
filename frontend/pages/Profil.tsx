import { useContext, useState } from "react";
import { AuthContext } from "../contexts/auth.context";
import AvatarUpload from "../components/AvatarUpload";
import axios from "axios";
import { toast } from "react-hot-toast";

const Profil = () => {
  const { user, setUser } = useContext(AuthContext);
  const [isEditing, setIsEditing] = useState(false);
  const [formData, setFormData] = useState({
    nom: user?.nom || "",
    prenom: user?.prenom || "",
    email: user?.email || "",
  });

  if (!user) {
    return <div className="p-6 text-center">Chargement...</div>;
  }

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async () => {
    try {
      const res = await axios.post(
        "http://localhost:8000/user/me",
        formData,
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`,
            "Content-Type": "application/json",
          },
        }
      );

      if (res.data.success) {
        toast.success(res.data.message);
        setUser(res.data.user);
        localStorage.setItem("user", JSON.stringify(res.data.user));
        setIsEditing(false);
      } else {
        toast.error(res.data.message);
      }
    } catch {
      toast.error("Erreur lors de la mise à jour du profil");
    }
  };

  return (
    <div className="max-w-3xl mx-auto p-6">
      <div className="bg-white shadow-lg rounded-2xl p-8">
        {/* Avatar */}
        <div className="flex flex-col items-center">
          <img
            src={`http://localhost:8000${user.avatar}`}
            alt="Avatar"
            className="w-28 h-28 rounded-full border-4 border-[#daf020] shadow-md"
          />
          <h1 className="mt-4 text-2xl font-bold text-[#daf020]">
            {user.prenom} {user.nom}
          </h1>
          <p className="text-gray-600">{user.email}</p>
          <span className="mt-2 px-3 py-1 text-sm rounded-full bg-black text-[#daf020] font-semibold">
            {user.roles.includes("ROLE_ADMIN") ? "Administrateur" : "Utilisateur"}
          </span>
        </div>

        {/* Infos supplémentaires */}
        <div className="mt-8 space-y-3 text-gray-700">
          <p>
            📅 CGU acceptées le :{" "}
            <strong>
              {user.dateCGU
                ? new Date(user.dateCGU).toLocaleDateString("fr-FR")
                : "Non renseigné"}
            </strong>
          </p>
          <p>
            ✅ Statut :{" "}
            <strong>
              {user.isActive ? "Compte actif" : "En attente d’activation"}
            </strong>
          </p>
        </div>

        {/* Formulaire d’édition */}
        <div className="mt-8">
          {!isEditing ? (
            <button
              onClick={() => setIsEditing(true)}
              className="px-6 py-2 bg-black text-[#daf020] rounded-xl font-semibold hover:bg-[#daf020] hover:text-black transition"
            >
              Modifier le profil
            </button>
          ) : (
            <div className="space-y-4">
              <input
                type="text"
                name="prenom"
                value={formData.prenom}
                onChange={handleChange}
                className="input input-bordered w-full"
                placeholder="Prénom"
              />
              <input
                type="text"
                name="nom"
                value={formData.nom}
                onChange={handleChange}
                className="input input-bordered w-full"
                placeholder="Nom"
              />
              <input
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                className="input input-bordered w-full"
                placeholder="Email"
              />
              <div className="flex gap-4">
                <button
                  onClick={handleSubmit}
                  className="px-6 py-2 bg-[#daf020] text-black rounded-xl font-semibold hover:bg-black hover:text-[#daf020] transition"
                >
                  Sauvegarder
                </button>
                <button
                  onClick={() => setIsEditing(false)}
                  className="px-6 py-2 bg-gray-300 rounded-xl hover:bg-gray-400"
                >
                  Annuler
                </button>
              </div>
            </div>
          )}
        </div>

        {/* Upload avatar */}
        <div className="mt-8 flex justify-center gap-4">
          <AvatarUpload
            onUploaded={(newAvatar) => {
              const updatedUser = { ...user, avatar: newAvatar };
              setUser(updatedUser);
              localStorage.setItem("user", JSON.stringify(updatedUser));
            }}
          />
        </div>
      </div>
    </div>
  );
};

export default Profil;
