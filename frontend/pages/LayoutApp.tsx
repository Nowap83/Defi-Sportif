
import { Outlet } from 'react-router-dom'
import Footer from '../components/Footer'
import Header from '../components/Header'
import { AuthContext } from '../contexts/auth.context'
import { useContext } from 'react'
import Sidebar from '../components/Sidebar'



const LayoutApp = () => {
  const { user } = useContext(AuthContext)
  console.log(user);
  return (
    <div className="min-h-screen flex flex-col">
      <Header />

      {user?.roles.includes("ROLE_ADMIN") && <Sidebar /> || !user}

      <main className="flex-grow">
        <Outlet />
      </main>

      <Footer />
    </div>
  )
}

export default LayoutApp