import { Route, Routes } from 'react-router'
import LayoutApp from '../pages/LayoutApp'
import Accueil from '../pages/Accueil'
import './App.css'

function App() {
  return (
    <Routes>
      <Route path='/' element={<LayoutApp />}>
        <Route path='/' element={<Accueil />} />



      </Route>
    </Routes>
  )
}

export default App
