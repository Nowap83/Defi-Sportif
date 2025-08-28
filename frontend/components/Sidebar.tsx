import { Link } from "react-router-dom";

export default function Sidebar() {
  return (
    <aside className="w-64 bg-green shadow-lg p-4">
      <nav className="space-y-4">
        <Link to="/defis" className="block hover:text-blue-600">
          📌 Gérer les défis
        </Link>
        <Link to="/admin/users" className="block hover:text-blue-600">
          👤 Gérer les utilisateurs
        </Link>
        <Link to="/admin/stats" className="block hover:text-blue-600">
          📊 Statistiques
        </Link>
      </nav>
    </aside>
  );
}
