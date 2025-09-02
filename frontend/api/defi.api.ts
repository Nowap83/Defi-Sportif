import axios from "axios";

const API_BASE_URL = process.env.REACT_APP_API_URL || "http://91.168.22.101/api";

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

export const getDefis = async (token: string): Promise<Defi[]> => {
  console.log("Getting defis with token:", token);
  try {
    const res = await axios.get(`${API_BASE_URL}/defis`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    console.log("Get defis successful");
    return res.data;
  } catch (error) {
    console.error("Get defis failed:", error);
    throw error;
  }
};

export const getDefi = async (id: number, token: string): Promise<Defi> => {
  try {
    const res = await axios.get(`${API_BASE_URL}/defis/${id}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    return res.data;
  } catch (error) {
    console.error(`Get defi ${id} failed:`, error);
    throw error;
  }
};

export const deleteDefi = async (id: number, token: string): Promise<void> => {
  try {
    await axios.delete(`${API_BASE_URL}/defis/${id}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    console.log(`Defi ${id} deleted successfully`);
  } catch (error) {
    console.error(`Delete defi ${id} failed:`, error);
    throw error;
  }
};

export const updateDefi = async (id: number, updatedData: Partial<Defi>, token: string): Promise<Defi> => {
  try {
    const res = await axios.put(`${API_BASE_URL}/defis/${id}`, updatedData, {
      headers: { Authorization: `Bearer ${token}` },
    });
    console.log(`Defi ${id} updated successfully`);
    return res.data;
  } catch (error) {
    console.error(`Update defi ${id} failed:`, error);
    throw error;
  }
};

export const createDefi = async (data: FormData, token: string): Promise<Defi> => {
  // Log des données pour le débogage
  for (let [key, value] of data.entries()) {
    console.log(key, value);
  }

  try {
    const response = await axios.post(`${API_BASE_URL}/defis`, data, {
      headers: { 
        Authorization: `Bearer ${token}`,
        "Content-Type": "multipart/form-data",
      },
    });
    console.log("Defi created successfully");
    return response.data;
  } catch (error: any) {
    console.error("Error creating defi:", error);
    throw error;
  }
};