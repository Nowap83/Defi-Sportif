import { useContext, useState } from "react";
import { toast } from "react-hot-toast";
import { AuthContext } from "../contexts/auth.context";
import { Plus } from "lucide-react";
import { createDefi } from "../api/defi.api";

const CreateDefiForm = ({ onDefiCreated }: { onDefiCreated: () => void }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [formData, setFormData] = useState({
    titre: "",
    description: "",
    dateDefi: "",
    typeDefi: "",
    region: "",
    pays: "",
    distance: "",
    minParticipant: "",
    maxParticipant: "",
  });
  const [image, setImage] = useState<File | null>(null);
  const [loading, setLoading] = useState(false);

  const { token } = useContext(AuthContext);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setImage(e.target.files[0]);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!token) return;
    setLoading(true);

    try {
      const data = new FormData();

      Object.entries(formData).forEach(([key, value]) => {
        if (value !== "") {
          if (["distance", "minParticipant", "maxParticipant"].includes(key)) {
            data.append(key, String(Number(value)));
          } else {
            data.append(key, value);
          }
        }
      });

      if (image) {
        data.append("image", image);
      }

      await createDefi(data, token);

      toast.success("Défi créé avec succès ✅");
      onDefiCreated();

      setFormData({
        titre: "",
        description: "",
        dateDefi: "",
        typeDefi: "",
        region: "",
        pays: "",
        distance: "",
        minParticipant: "",
        maxParticipant: "",
      });
      setImage(null);
      setIsOpen(false);
    } catch (err) {
      console.error(err);
      toast.error("Erreur lors de la création du défi ❌");
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <button
        onClick={() => setIsOpen(true)}
        className="flex items-center gap-2 px-4 py-2 bg-[#daf020] text-black rounded-xl font-semibold hover:bg-black hover:text-[#daf020] transition"
      >
        <Plus size={20} /> Nouveau défi
      </button>

      {isOpen && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
          <div className="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg">
            <h2 className="text-xl font-bold mb-4">➕ Créer un défi</h2>
            <form onSubmit={handleSubmit} className="grid gap-3">
              <input
                type="text"
                name="titre"
                value={formData.titre}
                onChange={handleChange}
                placeholder="Titre"
                required
                className="input input-bordered w-full"
              />
              <textarea
                name="description"
                value={formData.description}
                onChange={handleChange}
                placeholder="Description"
                required
                className="textarea textarea-bordered w-full"
              />
              <input
                type="date"
                name="dateDefi"
                value={formData.dateDefi}
                onChange={handleChange}
                required
                className="input input-bordered w-full"
              />
              <input
                type="text"
                name="typeDefi"
                value={formData.typeDefi}
                onChange={handleChange}
                placeholder="Type de défi"
                required
                className="input input-bordered w-full"
              />
              <input
                type="text"
                name="region"
                value={formData.region}
                onChange={handleChange}
                placeholder="Région"
                className="input input-bordered w-full"
              />
              <input
                type="text"
                name="pays"
                value={formData.pays}
                onChange={handleChange}
                placeholder="Pays"
                className="input input-bordered w-full"
              />
              <input
                type="number"
                step="0.1"
                name="distance"
                value={formData.distance}
                onChange={handleChange}
                placeholder="Distance (km)"
                className="input input-bordered w-full"
              />
              <div className="grid grid-cols-2 gap-3">
                <input
                  type="number"
                  name="minParticipant"
                  value={formData.minParticipant}
                  onChange={handleChange}
                  placeholder="Min participants"
                  className="input input-bordered w-full"
                />
                <input
                  type="number"
                  name="maxParticipant"
                  value={formData.maxParticipant}
                  onChange={handleChange}
                  placeholder="Max participants"
                  className="input input-bordered w-full"
                />
              </div>
              <input
                type="file"
                accept="image/*"
                onChange={handleFileChange}
                className="file-input file-input-bordered w-full"
              />
              <div className="flex justify-end gap-3 mt-4">
                <button
                  type="button"
                  onClick={() => setIsOpen(false)}
                  className="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400"
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  disabled={loading}
                  className="px-6 py-2 bg-[#daf020] text-black rounded-lg font-semibold hover:bg-black hover:text-[#daf020] transition"
                >
                  {loading ? "Création..." : "Créer"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
};

export default CreateDefiForm;
