import '../src/index.css'
const Accueil = () => {
  return (
    <div className="container mx-auto px-4 py-12">
      <div className="text-center mb-15">
        <img src="/AgoraFit.png" alt="Logo AgoraFit" className="h-70 w-70 rounded-full object-cover mx-auto mb-10" />
        <h1 className="text-5xl font-bold text-primary">
          Bienvenue sur <span className="agora font-helios">AgoraFit</span>
        </h1>
        <p className="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
          Relevez des défis sportifs, suivez vos performances et partagez votre énergie avec la communauté.
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="card bg-base-200 shadow-xl p-6">
          <h2 className="text-xl font-semibold mb-2"> Participez à des défis</h2>
          <p>
            Découvrez une variété de défis sportifs créés par la communauté et par les organisateurs.
            Inscrivez-vous et mettez vos capacités à l’épreuve.
          </p>
        </div>

        <div className="card bg-base-200 shadow-xl p-6">
          <h2 className="text-xl font-semibold mb-2"> Suivez vos performances</h2>
          <p>
            Enregistrez vos résultats après chaque défi : temps, distance, réussite. 
            Visualisez vos progrès grâce aux statistiques et graphiques interactifs.
          </p>
        </div>

        <div className="card bg-base-200 shadow-xl p-6">
          <h2 className="text-xl font-semibold mb-2"> Rejoignez la communauté</h2>
          <p>
            Connectez-vous avec d’autres passionnés de sport, échangez vos expériences
            et inspirez-vous des performances de vos amis.
          </p>
        </div>
      </div>
    </div>
  );
};

export default Accueil;
