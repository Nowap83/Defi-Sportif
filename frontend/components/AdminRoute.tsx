import { Navigate } from "react-router-dom";
import { AuthContext } from "../contexts/auth.context";
import { useContext } from "react";

const AdminRoute = ({ children }: { children: React.ReactNode }) => {
  const { user } = useContext(AuthContext);

  if (!user) {
    return <Navigate to="/login" />;
  }

  if (!user.roles.includes("ROLE_ADMIN")) {
    return <Navigate to="/" />; 
  }

  return <>{children}</>;
};

export default AdminRoute;
