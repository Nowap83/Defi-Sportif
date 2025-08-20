import { Link } from "react-router-dom";
import '../src/index.css'

import LinkButton from "../components/Button";

const Header = () => {
  return (
    <div className="flex items-center justify-between bg-base-100 shadow-lg px-4 h-16">
      <div className="flex items-center gap-2">
        <img
          src="../src/assets/AgoraRunningBG.png"
          alt="Logo AgoraFit"
          className="h-10"
        />
        <Link to="/" className="btn btn-ghost normal-case text-xl title font-helios">
          AgoraFit
        </Link>
      </div>

      <div className="flex gap-2">
        <LinkButton to="/register" variant="primary">
          Inscription
        </LinkButton>
        <LinkButton to="/login" variant="secondary">
          Connexion
        </LinkButton>
      </div>
    </div>
  );
};

export default Header;
