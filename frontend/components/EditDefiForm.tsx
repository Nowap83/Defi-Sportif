import { useState } from "react";
import { type Defi } from "../api/defi.api";

interface EditDefiFormProps {
  defi: Defi;
  onSave: (id: number, updatedData: Partial<Defi>) => void;
  onClose: () => void;
}

const EditDefiForm: React.FC<EditDefiFormProps> = ({ defi, onSave, onClose }) => {
  const [formData, setFormData] = useState<Partial<Defi>>({
    titre: defi.titre,
    description: defi.description,
    dateDefi: defi.dateDefi,
    typeDefi: defi.typeDefi,
    region: defi.region,
    pays: defi.pays,
    distance: defi.distance,
    minParticipant: defi.minParticipant,
    maxParticipant: defi.maxParticipant,
    image: defi.image,
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSave(defi.id, formData);
    onClose();
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
      <div className="bg-white p-6 rounded-xl shadow-lg w-full max-w-lg">
        <h2 className="text-xl font-bold mb-4">Modifier le défi</h2>

        <form onSubmit={handleSubmit} className="space-y-4">
          <input
            type="text"
            name="titre"
            value={formData.titre}
            onChange={handleChange}
            placeholder="Titre"
            className="input input-bordered w-full"
          />

          <textarea
            name="description"
            value={formData.description}
            onChange={handleChange}
            placeholder="Description"
            className="textarea textarea-bordered w-full"
          />

          <input
            type="date"
            name="dateDefi"
            value={formData.dateDefi?.substring(0, 10)}
            onChange={handleChange}
            className="input input-bordered w-full"
          />

          <input
            type="text"
            name="typeDefi"
            value={formData.typeDefi}
            onChange={handleChange}
            placeholder="Type de défi"
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
            name="distance"
            value={formData.distance}
            onChange={handleChange}
            placeholder="Distance (km)"
            className="input input-bordered w-full"
          />

          <div className="grid grid-cols-2 gap-2">
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
            type="text"
            name="image"
            value={formData.image}
            onChange={handleChange}
            placeholder="URL de l’image"
            className="input input-bordered w-full"
          />

          <div className="flex justify-end gap-2 mt-4">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
            >
              Annuler
            </button>
            <button
              type="submit"
              className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Sauvegarder
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default EditDefiForm;
