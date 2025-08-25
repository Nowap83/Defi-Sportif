import { useContext } from "react";
import { AuthContext } from "../contexts/auth.context";
import AvatarUpload from "../components/AvatarUpload";

const Profil = () => {
  const { user, setUser } = useContext(AuthContext);

  if (!user) {
    return <div className="p-6 text-center">Chargement...</div>;
  }

  console.log(user.avatar)

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
            <strong>{user.isActive ? "Compte actif" : "En attente d’activation"}</strong>
          </p>
        </div>

        {/* Actions */}
        <div className="mt-8 flex justify-center gap-4">
          <AvatarUpload
            onUploaded={(newAvatar) => {
              const updatedUser = { ...user, avatar: newAvatar };
              setUser(updatedUser);
              localStorage.setItem("user", JSON.stringify(updatedUser)); // 🔥 sauvegarde
            }}
          />


        </div>
      </div>
    </div>
  );
};

export default Profil;
