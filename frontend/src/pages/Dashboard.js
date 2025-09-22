import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { tripService } from '../services/api';

const Dashboard = () => {
  const { user } = useAuth();
  const [driverTrips, setDriverTrips] = useState([]);
  const [passengerTrips, setPassengerTrips] = useState([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('driver');

  useEffect(() => {
    const fetchTrips = async () => {
      try {
        const [driverResponse, passengerResponse] = await Promise.all([
          tripService.getDriverTrips(),
          tripService.getPassengerTrips()
        ]);
        
        setDriverTrips(driverResponse.data);
        setPassengerTrips(passengerResponse.data);
      } catch (error) {
        console.error('Error fetching trips:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchTrips();
  }, []);

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getStatusBadge = (status) => {
    const badges = {
      active: 'bg-green-100 text-green-800',
      completed: 'bg-blue-100 text-blue-800',
      cancelled: 'bg-red-100 text-red-800',
      pending: 'bg-yellow-100 text-yellow-800',
      confirmed: 'bg-green-100 text-green-800'
    };
    
    const labels = {
      active: 'Actif',
      completed: 'Terminé',
      cancelled: 'Annulé',
      pending: 'En attente',
      confirmed: 'Confirmé'
    };

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${badges[status] || 'bg-gray-100 text-gray-800'}`}>
        {labels[status] || status}
      </span>
    );
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-eco"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">
            Bonjour, {user?.name} ! 👋
          </h1>
          <p className="text-gray-600 mt-2">
            Voici un aperçu de vos trajets et activités
          </p>
        </div>

        {/* Quick Stats */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div className="card text-center">
            <div className="text-2xl font-bold text-eco">{driverTrips.length}</div>
            <div className="text-sm text-gray-600">Trajets proposés</div>
          </div>
          <div className="card text-center">
            <div className="text-2xl font-bold text-eco">{passengerTrips.length}</div>
            <div className="text-sm text-gray-600">Trajets réservés</div>
          </div>
          <div className="card text-center">
            <div className="text-2xl font-bold text-eco">{user?.eco_credits || 0}</div>
            <div className="text-sm text-gray-600">Crédits éco</div>
          </div>
          <div className="card text-center">
            <div className="text-2xl font-bold text-eco">4.8</div>
            <div className="text-sm text-gray-600">Note moyenne</div>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <Link to="/create-trip" className="card hover:shadow-xl transition-shadow cursor-pointer">
            <div className="flex items-center space-x-4">
              <div className="text-4xl">🚗</div>
              <div>
                <h3 className="text-lg font-semibold text-gray-900">Publier un trajet</h3>
                <p className="text-gray-600">Proposez un trajet et partagez vos frais</p>
              </div>
            </div>
          </Link>
          
          <Link to="/search" className="card hover:shadow-xl transition-shadow cursor-pointer">
            <div className="flex items-center space-x-4">
              <div className="text-4xl">🔍</div>
              <div>
                <h3 className="text-lg font-semibold text-gray-900">Rechercher un trajet</h3>
                <p className="text-gray-600">Trouvez un trajet qui vous convient</p>
              </div>
            </div>
          </Link>
        </div>

        {/* Trips Tabs */}
        <div className="card">
          <div className="border-b border-gray-200 mb-6">
            <nav className="-mb-px flex space-x-8">
              <button
                onClick={() => setActiveTab('driver')}
                className={`py-2 px-1 border-b-2 font-medium text-sm ${
                  activeTab === 'driver'
                    ? 'border-eco text-eco'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Mes trajets proposés ({driverTrips.length})
              </button>
              <button
                onClick={() => setActiveTab('passenger')}
                className={`py-2 px-1 border-b-2 font-medium text-sm ${
                  activeTab === 'passenger'
                    ? 'border-eco text-eco'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Mes réservations ({passengerTrips.length})
              </button>
            </nav>
          </div>

          {/* Driver Trips */}
          {activeTab === 'driver' && (
            <div className="space-y-4">
              {driverTrips.length === 0 ? (
                <div className="text-center py-8">
                  <div className="text-6xl mb-4">🚗</div>
                  <h3 className="text-lg font-medium text-gray-900 mb-2">
                    Aucun trajet proposé
                  </h3>
                  <p className="text-gray-600 mb-4">
                    Commencez par proposer votre premier trajet
                  </p>
                  <Link to="/create-trip" className="btn-eco">
                    Publier un trajet
                  </Link>
                </div>
              ) : (
                driverTrips.map((trip) => (
                  <div key={trip.id} className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div className="flex justify-between items-start mb-2">
                      <div className="flex-1">
                        <h3 className="font-semibold text-lg">
                          {trip.departure_city} → {trip.arrival_city}
                        </h3>
                        <p className="text-gray-600">
                          {formatDate(trip.departure_time)}
                        </p>
                      </div>
                      <div className="text-right">
                        {getStatusBadge(trip.status)}
                        <div className="text-eco font-semibold mt-1">
                          {trip.price_per_seat}€ / place
                        </div>
                      </div>
                    </div>
                    
                    <div className="flex items-center justify-between text-sm text-gray-600">
                      <div className="flex items-center space-x-4">
                        <span>🚙 {trip.make} {trip.model}</span>
                        <span>👥 {trip.confirmed_passengers || 0}/{trip.available_seats} places</span>
                      </div>
                      <Link
                        to={`/trips/${trip.id}`}
                        className="text-eco hover:text-eco-dark font-medium"
                      >
                        Voir les détails →
                      </Link>
                    </div>
                  </div>
                ))
              )}
            </div>
          )}

          {/* Passenger Trips */}
          {activeTab === 'passenger' && (
            <div className="space-y-4">
              {passengerTrips.length === 0 ? (
                <div className="text-center py-8">
                  <div className="text-6xl mb-4">🎒</div>
                  <h3 className="text-lg font-medium text-gray-900 mb-2">
                    Aucune réservation
                  </h3>
                  <p className="text-gray-600 mb-4">
                    Trouvez votre prochain trajet
                  </p>
                  <Link to="/search" className="btn-eco">
                    Rechercher un trajet
                  </Link>
                </div>
              ) : (
                passengerTrips.map((trip) => (
                  <div key={trip.id} className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div className="flex justify-between items-start mb-2">
                      <div className="flex-1">
                        <h3 className="font-semibold text-lg">
                          {trip.departure_city} → {trip.arrival_city}
                        </h3>
                        <p className="text-gray-600">
                          {formatDate(trip.departure_time)}
                        </p>
                        <p className="text-sm text-gray-500">
                          Conducteur: {trip.driver_name}
                        </p>
                      </div>
                      <div className="text-right">
                        {getStatusBadge(trip.participation_status)}
                        <div className="text-eco font-semibold mt-1">
                          {trip.total_price}€
                        </div>
                      </div>
                    </div>
                    
                    <div className="flex items-center justify-between text-sm text-gray-600">
                      <div className="flex items-center space-x-4">
                        <span>🚙 {trip.make} {trip.model}</span>
                        <span>👥 {trip.seats_reserved} place(s) réservée(s)</span>
                      </div>
                      <Link
                        to={`/trips/${trip.id}`}
                        className="text-eco hover:text-eco-dark font-medium"
                      >
                        Voir les détails →
                      </Link>
                    </div>
                  </div>
                ))
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default Dashboard;