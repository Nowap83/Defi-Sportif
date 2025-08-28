import { Link, useNavigate } from "react-router-dom";
import "../src/index.css";
import LinkButton from "../components/Button";
import { AuthContext } from '../contexts/auth.context'
import { toast } from "react-hot-toast";
import { useContext } from "react";

const Header = () => {
  const { user, token, setToken, setUser } = useContext(AuthContext)
  const navigate = useNavigate();

  const handleLogout = () => {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    setToken(null);
    setUser(null);
    toast.success("Déconnexion réussie");
    navigate("/login");
  };

  return (
    <div className="flex items-center justify-between bg-base-100 shadow-lg px-4 h-16">
      <div className="flex items-center gap-2">
        <img
          src="../src/assets/AgoraRunningBG.png"
          alt="Logo AgoraFit"
          className="h-10"
        />
        <Link
          to="/"
          className="btn btn-ghost normal-case text-xl title font-helios"
        >
          AgoraFit
        </Link>
        {user?.roles?.includes("ROLE_USER") && !user.roles.includes("ROLE_ADMIN") && (
          <Link
            to="/defis"
            className="px-6 py-2 rounded-2xl font-semibold transition-all duration-300 bg-[#daf020] text-black hover:bg-black hover:text-[#daf020]"
          >
            Défis
          </Link>
        )}



      </div>

      <div className="flex gap-2 items-center">
        {token ? (
          <>
            <Link to="/profil" className="px-6 py-2 rounded-2xl font-semibold transition-all duration-300 bg-[#daf020] text-black hover:bg-black hover:text-[#daf020]">
              Profil
            </Link>
            <button
              onClick={handleLogout}
              className="px-6 py-2 rounded-2xl font-semibold transition-all duration-300 bg-red-500 text-white hover:bg-red-600"
            >
              Déconnexion
            </button>
          </>
        ) : (
          <>
            <LinkButton to="/register" variant="primary">
              Inscription
            </LinkButton>
            <LinkButton to="/login" variant="secondary">
              Connexion
            </LinkButton>
          </>
        )}
      </div>
    </div>
  );
};

export default Header;
