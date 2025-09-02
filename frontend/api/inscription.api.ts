import axios from "axios";

const API_URL = process.env.REACT_APP_API_URL || "http://91.168.22.101/api"

export const inscrireAUnDefi = async (defiId: number, token: string) => {
  const res = await axios.post(
    `${API_URL}/inscriptions/defis/${defiId}`,
    {},
    {
      headers: { Authorization: `Bearer ${token}` },
    }
  );
  return res.data;
};

export const getInscriptionsDefi = async (defiId: number, token: string) => {
  const res = await axios.get(`${API_URL}/inscriptions/defis/${defiId}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.data;
};

export const deleteInscription = async (inscriptionId: number, token: string) => {
  const res = await axios.delete(`${API_URL}/inscriptions/${inscriptionId}`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.data;
};

export const requestCancelInscription = async (inscriptionId: number, token: string) => {
  const res = await axios.post(
    `${API_URL}/inscriptions/${inscriptionId}/request-cancel`,
    {},
    {
      headers: { Authorization: `Bearer ${token}` },
    }
  );
  return res.data;
};

export const checkMyInscription = async (defiId: number, token: string) => {
  const res = await axios.get(`${API_URL}/inscriptions/defis/${defiId}/me`, {
    headers: { Authorization: `Bearer ${token}` },
  });
  return res.data; 
};

