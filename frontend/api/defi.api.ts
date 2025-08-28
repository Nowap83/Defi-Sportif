import axios from "axios";

const API_URL = "http://localhost:8000/defis";

export interface Defi {
  id: number;
  titre: string;
  description: string;
  dateDefi: string;
  typeDefi: string;
  region: string;
  pays: string;
  distance: string;
  minParticipant: string;
  maxParticipant: string;
  image: string;
}

export const getDefis = async (token: string) => {
  console.log("Getting defis with token:", token);
  try {
    const res = await axios.get(API_URL, {
      headers: { Authorization: `Bearer ${token}` },
    });
    console.log("Get defis successful");
    return res.data;
  } catch (error) {
    console.error("Get defis failed:", error);
    throw error;
  }
};

export const deleteDefi = async (id: number, token: string) => {
  return axios.delete(`${API_URL}/${id}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
};

export const updateDefi = async (token: string, id: number, updatedData: any): Promise<Defi> => {
  const res = await axios.put(`${API_URL}/${id}`, updatedData, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.data.defi;
};

export const createDefi = async (data: FormData, token: string) => {
  console.log("TOKEN USED IN createDefi:", token);
  console.log("FormData contents:");
  for (let [key, value] of data.entries()) {
    console.log(key, value);
  }

  try {
    const response = await axios.post(API_URL, data, {
      headers: { 
        Authorization: `Bearer ${token}`,
        // Don't set Content-Type for FormData - let axios set it automatically with boundary
      },
    });
    return response;
  } catch (error: any) {
    console.error("API Error:", error);
    if (error.response) {
      console.error("Error response:", error.response.data);
      console.error("Error status:", error.response.status);
      console.error("Error headers:", error.response.headers);
    }
    throw error;
  }
};