import { useEffect, useState, useContext } from "react";
import { AuthContext } from "../contexts/auth.context";
import CreateDefiForm from "../components/CreateDefiForm";
import { getDefis, deleteDefi, updateDefi } from "../api/defi.api";
import { toast } from "react-hot-toast";
import { type Defi } from "../api/defi.api";
import EditDefiForm from "../components/EditDefiForm";
import { inscrireAUnDefi, requestCancelInscription, checkMyInscription } from "../api/inscription.api";

const Defis = () => {
  const { user, token } = useContext(AuthContext);
  const [defis, setDefis] = useState<Defi[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [editingDefi, setEditingDefi] = useState<Defi | null>(null);
  const [inscriptions, setInscriptions] = useState<Record<number, boolean>>({});

  const fetchDefis = async () => {
    try {
      if (!token) return;
      const data = await getDefis(token);
      setDefis(data);

      // Vérifie l'inscription de l'utilisateur à chaque défi
      if (user?.roles.includes("ROLE_USER")) {
        const status: Record<number, boolean> = {};
        for (const defi of data) {
          try {
            const res = await checkMyInscription(defi.id, token);
            status[defi.id] = !!res; // true si inscrit, false sinon
          } catch {
            status[defi.id] = false;
          }
        }
        setInscriptions(status);
      }
    } catch (err) {
      console.error("Erreur lors du chargement des défis", err);
    } finally {
      setLoading(false);
    }
  };

  const handleInscrire = async (id: number) => {
    try {
      await inscrireAUnDefi(id, token!);
      toast.success("Inscription réussie ✅");
      fetchDefis();
    } catch {
      toast.error("Erreur lors de l'inscription ❌");
    }
  };

  const handleAnnuler = async (id: number) => {
    try {
      await requestCancelInscription(id, token!);
      toast.success("Demande d'annulation envoyée ✅");
      fetchDefis();
    } catch {
      toast.error("Erreur lors de l'annulation ❌");
    }
  };
  const handleDelete = async (id: number) => {
    if (!window.confirm("Voulez-vous vraiment supprimer ce défi ?")) return;

    try {
      await deleteDefi(id, token!);
      setDefis(defis.filter((d) => d.id !== id));
      toast.success("Défi supprimé ✅");
    } catch {
      toast.error("Erreur lors de la suppression ❌");
    }
  };

  const handleEdit = async (id: number, updatedData: Partial<Defi>) => {
  if (!token) return;

  try {
    const updatedDefi = await updateDefi( id, updatedData, token);

    setDefis((prevDefis) =>
      prevDefis.map((d) => (d.id === id ? updatedDefi : d))
    );

    toast.success("Défi modifié avec succès ✅");
    fetchDefis();
  } catch (err) {
    console.error("Erreur modification défi", err);
    toast.error("Erreur lors de la modification ❌");
  }
};

  useEffect(() => {
    fetchDefis();
  }, []);

  if (loading) {
    return <p className="text-center mt-6">Chargement des défis...</p>;
  }

  const filteredDefis = defis.filter((defi) =>
    `${defi.titre} ${defi.description} ${defi.region ?? ""} ${defi.pays ?? ""}`
      .toLowerCase()
      .includes(search.toLowerCase())
  );

  return (
    <div className="max-w-4xl mx-auto p-6">
      <h1 className="text-3xl font-bold mb-6 text-[#daf020]">Liste des défis</h1>

      {user?.roles.includes("ROLE_ADMIN") && (
        <CreateDefiForm onDefiCreated={fetchDefis} />
      )}

      <input
        type="text"
        placeholder="Rechercher un défi..."
        value={search}
        onChange={(e) => setSearch(e.target.value)}
        className="input input-bordered w-full mb-6"
      />

      {defis.length === 0 ? (
        <p className="text-center text-gray-500 mt-10">🚫 Pas de défi actuellement</p>
      ) : (
        <ul className="space-y-4">
          {filteredDefis.map((defi) => (
            <li key={defi.id} className="p-4 bg-white shadow rounded-xl">
              <div className="flex flex-col md:flex-row gap-4">
                {defi.image && (
                  <div className="md:w-1/4">
                    <img
                      src={`http://localhost:8000${defi.image}`}
                      alt={defi.titre}
                      className="w-full h-40 object-cover rounded-lg"
                    />
                  </div>
                )}

                <div className={`${defi.image ? "md:w-3/4" : "w-full"}`}>
                  <h2 className="text-xl font-semibold text-blue-800">{defi.titre}</h2>
                  <p className="text-gray-600 mt-1">{defi.description}</p>

                  {/* Infos défi */}
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3">
                    <div className="text-sm">📅 {new Date(defi.dateDefi).toLocaleDateString("fr-FR")}</div>
                    <div className="text-sm">🏃 {defi.typeDefi}</div>
                    <div className="text-sm">📍 {defi.region}</div>
                    <div className="text-sm">🇫🇷 {defi.pays}</div>
                    <div className="text-sm">📏 {defi.distance} km</div>
                    <div className="text-sm">👥 {defi.minParticipant} - {defi.maxParticipant}</div>
                  </div>
                </div>
              </div>

              {/* Boutons pour les users */}
              {user?.roles.includes("ROLE_USER") && (
                <div className="mt-4 flex gap-2 justify-end border-t pt-3">
                  {!inscriptions[defi.id] ? (
                    <button
                      onClick={() => handleInscrire(defi.id)}
                      className="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm"
                    >
                      ✅ S'inscrire
                    </button>
                  ) : (
                    <button
                      onClick={() => handleAnnuler(defi.id)}
                      className="px-3 py-1 bg-orange-500 text-white rounded hover:bg-orange-600 text-sm"
                    >
                      ❌ Demander annulation
                    </button>
                  )}
                </div>
              )}

              {/* Boutons pour les admins */}
              {user?.roles.includes("ROLE_ADMIN") && (
                <div className="mt-4 flex gap-2 justify-end border-t pt-3">
                  <button
                    onClick={() => setEditingDefi(defi)}
                    className="px-3 py-1 bg-yellow-400 rounded hover:bg-yellow-500 text-sm"
                  >
                    ✏️ Modifier
                  </button>
                  {editingDefi && (
                    <EditDefiForm
                      defi={editingDefi}
                      onSave={handleEdit}
                      onClose={() => setEditingDefi(null)}
                    />
                  )}
                  <button
                    onClick={() => handleDelete(defi.id)}
                    className="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
                  >
                    🗑️ Supprimer
                  </button>
                </div>
              )}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export default Defis;
