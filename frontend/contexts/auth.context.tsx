import React, { createContext, useState, type ReactNode } from "react";

export interface User {
  id: string;
  nom: string;
  prenom: string;
  roles: string[];
  isActive: boolean;
  dateCGU: Date;
  email: string;
  createdAt: Date;
  avatar: string;

}

interface AuthContextType {
  token: string | null;
  user: User | null;
  setToken: (token: string | null) => void;
  setUser: (user: User | null) => void;
}

export const AuthContext = createContext<AuthContextType>({
  token: null,
  user: null,
  setToken: () => {},
  setUser: () => {},
});

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [token, setToken] = useState<string | null>(
    localStorage.getItem("token")
  );
  const [user, setUser] = useState<User | null>(
    localStorage.getItem("user") ? JSON.parse(localStorage.getItem("user") as string) : null
  );

  return (
    <AuthContext.Provider value={{ token, user, setToken, setUser }}>
      {children}
    </AuthContext.Provider>
  );
};
