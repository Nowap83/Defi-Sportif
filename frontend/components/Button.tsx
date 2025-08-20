import React from "react";
import { Link } from "react-router-dom";

type ButtonProps = {
  children: React.ReactNode;
  to: string; // chemin de navigation
  variant?: "primary" | "secondary";
};

const LinkButton: React.FC<ButtonProps> = ({ children, to, variant = "primary" }) => {
  const baseClasses =
    "inline-block px-6 py-3 rounded-2xl font-semibold transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2";

  const variants = {
    primary:
      "bg-[#daf020] text-black hover:bg-black hover:text-[#daf020] focus:ring-[#daf020]",
    secondary:
      "bg-black text-[#daf020] hover:bg-[#daf020] hover:text-black focus:ring-black",
  };

  return (
    <Link to={to} className={`${baseClasses} ${variants[variant]}`}>
      {children}
    </Link>
  );
};

export default LinkButton;
