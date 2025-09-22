import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const Home = () => {
  const { isAuthenticated } = useAuth();
  const navigate = useNavigate();
  const [searchForm, setSearchForm] = useState({
    from: '',
    to: '',
    date: '',
    passengers: 1
  });

  const handleSearchChange = (e) => {
    setSearchForm({
      ...searchForm,
      [e.target.name]: e.target.value
    });
  };

  const handleSearch = (e) => {
    e.preventDefault();
    const searchParams = new URLSearchParams();
    if (searchForm.from) searchParams.append('from', searchForm.from);
    if (searchForm.to) searchParams.append('to', searchForm.to);
    if (searchForm.date) searchParams.append('date', searchForm.date);
    if (searchForm.passengers) searchParams.append('seats', searchForm.passengers);
    
    navigate(`/search?${searchParams.toString()}`);
  };

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="bg-gradient-to-br from-eco-light to-eco text-white py-20 px-4">
        <div className="max-w-7xl mx-auto text-center">
          <h1 className="text-5xl md:text-6xl font-bold mb-6">
            🌱 Voyagez <span className="text-yellow-300">écologique</span>
          </h1>
          <p className="text-xl md:text-2xl mb-8 opacity-90">
            Partagez vos trajets, réduisez votre empreinte carbone et faites des économies !
          </p>
          
          {/* Search Form */}
          <div className="bg-white rounded-2xl p-6 md:p-8 shadow-2xl max-w-4xl mx-auto">
            <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div className="md:col-span-1">
                <label className="block text-sm font-medium text-gray-700 mb-1">Départ</label>
                <input
                  type="text"
                  name="from"
                  value={searchForm.from}
                  onChange={handleSearchChange}
                  placeholder="Ville de départ"
                  className="input-eco"
                />
              </div>
              <div className="md:col-span-1">
                <label className="block text-sm font-medium text-gray-700 mb-1">Arrivée</label>
                <input
                  type="text"
                  name="to"
                  value={searchForm.to}
                  onChange={handleSearchChange}
                  placeholder="Ville d'arrivée"
                  className="input-eco"
                />
              </div>
              <div className="md:col-span-1">
                <label className="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input
                  type="date"
                  name="date"
                  value={searchForm.date}
                  onChange={handleSearchChange}
                  className="input-eco"
                  min={new Date().toISOString().split('T')[0]}
                />
              </div>
              <div className="md:col-span-1 flex flex-col justify-end">
                <button type="submit" className="btn-eco h-12">
                  🔍 Rechercher
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 px-4">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-4xl font-bold text-gray-800 mb-4">
              Pourquoi choisir EcoRide ?
            </h2>
            <p className="text-xl text-gray-600">
              Une solution complète pour un transport plus responsable
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="card text-center">
              <div className="text-5xl mb-4">🌍</div>
              <h3 className="text-xl font-semibold mb-3">Écologique</h3>
              <p className="text-gray-600">
                Réduisez votre empreinte carbone en partageant vos trajets.
                Moins de voitures sur la route = moins de pollution !
              </p>
            </div>

            <div className="card text-center">
              <div className="text-5xl mb-4">💰</div>
              <h3 className="text-xl font-semibold mb-3">Économique</h3>
              <p className="text-gray-600">
                Partagez les frais de carburant et réduisez vos coûts de transport.
                Gagnez de l'argent en tant que conducteur !
              </p>
            </div>

            <div className="card text-center">
              <div className="text-5xl mb-4">👥</div>
              <h3 className="text-xl font-semibold mb-3">Social</h3>
              <p className="text-gray-600">
                Rencontrez de nouvelles personnes et créez des liens
                pendant vos trajets partagés.
              </p>
            </div>

            <div className="card text-center">
              <div className="text-5xl mb-4">⭐</div>
              <h3 className="text-xl font-semibold mb-3">Crédits Éco</h3>
              <p className="text-gray-600">
                Gagnez des crédits écologiques à chaque trajet et
                débloquez des avantages exclusifs.
              </p>
            </div>

            <div className="card text-center">
              <div className="text-5xl mb-4">🔒</div>
              <h3 className="text-xl font-semibold mb-3">Sécurisé</h3>
              <p className="text-gray-600">
                Profils vérifiés, système d'évaluation et support client
                pour voyager en toute sécurité.
              </p>
            </div>

            <div className="card text-center">
              <div className="text-5xl mb-4">📱</div>
              <h3 className="text-xl font-semibold mb-3">Simple</h3>
              <p className="text-gray-600">
                Interface intuitive et processus de réservation
                rapide en quelques clics seulement.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="bg-gray-50 py-20 px-4">
        <div className="max-w-4xl mx-auto text-center">
          <h2 className="text-4xl font-bold text-gray-800 mb-6">
            Prêt à commencer votre voyage écologique ?
          </h2>
          <p className="text-xl text-gray-600 mb-8">
            Rejoignez des milliers d'utilisateurs qui font déjà la différence
          </p>
          
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            {isAuthenticated ? (
              <>
                <Link to="/search" className="btn-eco text-lg px-8 py-4">
                  Rechercher un trajet
                </Link>
                <Link to="/create-trip" className="btn-eco-outline text-lg px-8 py-4">
                  Publier un trajet
                </Link>
              </>
            ) : (
              <>
                <Link to="/register" className="btn-eco text-lg px-8 py-4">
                  S'inscrire gratuitement
                </Link>
                <Link to="/login" className="btn-eco-outline text-lg px-8 py-4">
                  Se connecter
                </Link>
              </>
            )}
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-20 px-4 bg-eco text-white">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
              <div className="text-4xl font-bold mb-2">1,234</div>
              <div className="text-lg opacity-90">Trajets partagés</div>
            </div>
            <div>
              <div className="text-4xl font-bold mb-2">567</div>
              <div className="text-lg opacity-90">Utilisateurs actifs</div>
            </div>
            <div>
              <div className="text-4xl font-bold mb-2">89%</div>
              <div className="text-lg opacity-90">CO² économisé</div>
            </div>
            <div>
              <div className="text-4xl font-bold mb-2">4.8/5</div>
              <div className="text-lg opacity-90">Note moyenne</div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};

export default Home;