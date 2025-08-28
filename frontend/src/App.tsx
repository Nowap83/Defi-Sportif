import { Route, Routes } from 'react-router'
import LayoutApp from '../pages/LayoutApp'
import Accueil from '../pages/Accueil'
import Login from '../pages/Login'
import Register from '../pages/Register'
import Profil from '../pages/Profil'
import Verify from '../pages/Verify'
import Defis from '../pages/Defis'
import AdminUsers from '../pages/AdminUsers'
import AdminRoute from '../components/AdminRoute'
import './App.css'
import { AuthProvider } from '../contexts/auth.context'

function App() {
  return (
    <AuthProvider>
      <Routes>
        <Route path='/' element={<LayoutApp />}>
          <Route index element={<Accueil />} />
          <Route path='login' element={<Login />} />
          <Route path='register' element={<Register />} />
          <Route path='profil' element={<Profil />} />
          <Route path='verify/:token' element={<Verify />} />
          <Route path='profil' element={<Profil />} />
          <Route path='defis' element={<Defis />} />

          <Route
            path='admin/users'
            element={
              <AdminRoute>
                <AdminUsers />   {/* page */}
              </AdminRoute>
            }
          />


        </Route>
      </Routes>
    </AuthProvider>
  )
}

export default App
