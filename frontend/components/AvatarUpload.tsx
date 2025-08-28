import { useContext, useState } from "react";
import axios from "axios";
import { AuthContext } from "../contexts/auth.context";

const AvatarUpload = ({ onUploaded }: { onUploaded: (url: string) => void }) => {
  const [file, setFile] = useState<File | null>(null);
  const { token } = useContext(AuthContext);

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      setFile(e.target.files[0]);
    }
  };

  const handleUpload = async () => {
    if (!file) return;

    const formData = new FormData();
    formData.append("avatar", file);

    const response = await axios.post(
      `http://localhost:8000/user/me/avatar`,
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
          Authorization: `Bearer ${token}`,
        },
      }
    );

    onUploaded(response.data.avatar); 
  };

  return (
    <div className="flex flex-col gap-2">
      <input type="file" onChange={handleFileChange} />
      <button
        onClick={handleUpload}
        className="px-4 py-2 rounded bg-[#daf020] hover:bg-black hover:text-[#daf020]"
      >
        Changer de photo de profil
      </button>
    </div>
  );
};

export default AvatarUpload;
