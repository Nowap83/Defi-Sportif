import React, { useContext, useEffect, useState } from "react";
import { AuthContext } from "../contexts/auth.context";
import axios from "axios";

interface User {
  id: number;
  email: string;
  roles: string[];
  nom: string;
  prenom: string;
  dateCGU: string;
  createdAt: string;
}

const AdminUsers: React.FC = () => {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);

  const { token } = useContext(AuthContext);

  useEffect(() => {
    const fetchUsers = async () => {
      try { 
        const response = await axios.get("http://localhost:8000/user", {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        setUsers(response.data);
      } catch (error) {
        console.error("Erreur lors de la récupération des utilisateurs :", error);
      } finally {
        setLoading(false);
      }
    };

    fetchUsers();
  }, []);

  if (loading) {
    return <p className="text-center mt-10">Chargement...</p>;
  }
const handleDelete = async (id: number) => {
    if (!window.confirm("Voulez-vous vraiment supprimer cet utilisateur ?")) {
      return;
    }

    try {
      await axios.delete(`http://localhost:8000/user/delete/${id}`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      setUsers((prev) => prev.filter((user) => user.id !== id));
    } catch (err) {
      console.error(err);
      alert("Erreur lors de la suppression de l'utilisateur");
    }
  };

  return (
    <div className="p-6">
      <h1 className="text-2xl font-bold mb-6">Gestion des Utilisateurs</h1>
      <div className="overflow-x-auto">
        <table className="min-w-full border border-gray-300 rounded-lg shadow-sm">
          <thead className="bg-black-100">
            <tr>
              <th className="px-4 py-2 border">ID</th>
              <th className="px-4 py-2 border">Email</th>
              <th className="px-4 py-2 border">Rôles</th>
              <th className="px-4 py-2 border">Nom</th>
              <th className="px-4 py-2 border">Prénom</th>
              <th className="px-4 py-2 border">Date CGU</th>
              <th className="px-4 py-2 border">Créé le</th>
              <th className="px-4 py-2 border">Actions</th>
            </tr>
          </thead>
          <tbody>
            {users.map((user) => (
              <tr key={user.id} className="bg-black">
                <td className="px-4 py-2 border text-center">{user.id}</td>
                <td className="px-4 py-2 border">{user.email}</td>
                <td className="px-4 py-2 border">
                  {user.roles.join(", ")}
                </td>
                <td className="px-4 py-2 border">{user.nom}</td>
                <td className="px-4 py-2 border">{user.prenom}</td>
                <td className="px-4 py-2 border">{user.dateCGU}</td>
                <td className="px-4 py-2 border">{user.createdAt}</td>
                <td className="px-4 py-2 border text-center">
                  <button
                    onClick={() => handleDelete(user.id)}
                    className="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-800"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default AdminUsers;