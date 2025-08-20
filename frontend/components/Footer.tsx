
import { Link } from "react-router-dom";

const Footer = () => {
  return (
    <footer className="bg-base-200 text-base-content py-10 mt-16">
      <div className="container mx-auto px-4 text-center md:text-left">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <h3 className="text-lg font-semibold mb-2">Navigation</h3>
            <ul className="space-y-1 text-sm">
              <li><Link to="/" className="link link-hover">Accueil</Link></li>
              <li><Link to="/login" className="link link-hover">Connexion</Link></li>
              <li><Link to="/register" className="link link-hover">Inscription</Link></li>
            </ul>
          </div>

          <div>
            <h3 className="text-lg font-semibold mb-2">Informations</h3>
            <p className="text-sm">© {new Date().getFullYear()} AgoraFit</p>
            <p className="text-sm">Tous droits réservés.</p>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;