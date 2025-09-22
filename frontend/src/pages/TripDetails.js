import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { tripService, participationService } from '../services/api';
import { useAuth } from '../context/AuthContext';

const TripDetails = () => {
  const { id } = useParams();
  const { user } = useAuth();
  const [trip, setTrip] = useState(null);
  const [loading, setLoading] = useState(true);
  const [joining, setJoining] = useState(false);
  const [seatsToBook, setSeatsToBook] = useState(1);

  useEffect(() => {
    const fetchTrip = async () => {
      try {
        const response = await tripService.getTripById(id);
        setTrip(response.data);
      } catch (error) {
        console.error('Error fetching trip:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchTrip();
  }, [id]);

  const handleJoinTrip = async () => {
    setJoining(true);
    try {
      await participationService.joinTrip(id, {
        seats_reserved: seatsToBook
      });
      
      // Refresh trip data
      const response = await tripService.getTripById(id);
      setTrip(response.data);
      
      alert('Demande de participation envoyée !');
    } catch (error) {
      console.error('Error joining trip:', error);
      alert('Erreur lors de la demande de participation');
    } finally {
      setJoining(false);
    }
  };

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

  const getFuelIcon = (fuelType) => {
    const icons = {
      electric: '⚡',
      hybrid: '🔋',
      diesel: '⛽',
      gasoline: '⛽'
    };
    return icons[fuelType] || '🚗';
  };

  const isDriverCurrentUser = trip && user && trip.driver_id === user.id;
  const hasUserParticipated = trip && user && trip.participations?.some(p => p.passenger_id === user.id);
  const availableSeats = trip ? trip.available_seats - (trip.participations?.filter(p => p.status === 'confirmed').reduce((sum, p) => sum + p.seats_reserved, 0) || 0) : 0;

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-eco"></div>
      </div>
    );
  }

  if (!trip) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="text-6xl mb-4">😕</div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Trajet introuvable</h2>
          <p className="text-gray-600">Ce trajet n'existe pas ou a été supprimé.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="card">
          {/* Header */}
          <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
            <div className="flex-1">
              <h1 className="text-3xl font-bold text-gray-900 mb-2">
                {trip.departure_city} → {trip.arrival_city}
              </h1>
              <p className="text-lg text-gray-600">
                {formatDate(trip.departure_time)}
              </p>
            </div>
            <div className="mt-4 lg:mt-0 text-center lg:text-right">
              <div className="text-3xl font-bold text-eco mb-1">
                {trip.price_per_seat}€
              </div>
              <div className="text-sm text-gray-500">par place</div>
            </div>
          </div>

          {/* Trip Details */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            {/* Left Column - Trip Info */}
            <div className="space-y-6">
              <div>
                <h3 className="text-lg font-semibold mb-3">Itinéraire</h3>
                <div className="space-y-4">
                  <div className="flex items-start space-x-3">
                    <div className="w-3 h-3 bg-eco rounded-full mt-2"></div>
                    <div>
                      <div className="font-medium">Départ</div>
                      <div className="text-gray-600">{trip.departure_address}</div>
                      <div className="text-sm text-eco">{formatDate(trip.departure_time)}</div>
                    </div>
                  </div>
                  <div className="flex items-start space-x-3">
                    <div className="w-3 h-3 bg-red-500 rounded-full mt-2"></div>
                    <div>
                      <div className="font-medium">Arrivée</div>
                      <div className="text-gray-600">{trip.arrival_address}</div>
                      {trip.arrival_time && (
                        <div className="text-sm text-gray-500">{formatDate(trip.arrival_time)}</div>
                      )}
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h3 className="text-lg font-semibold mb-3">Véhicule</h3>
                <div className="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                  <div className="text-3xl">{getFuelIcon(trip.fuel_type)}</div>
                  <div>
                    <div className="font-medium">{trip.make} {trip.model}</div>
                    <div className="text-sm text-gray-600">
                      {trip.year} • {trip.color} • {trip.vehicle_seats} places
                    </div>
                    <div className="text-sm text-eco capitalize">{trip.fuel_type}</div>
                  </div>
                </div>
              </div>

              {trip.description && (
                <div>
                  <h3 className="text-lg font-semibold mb-3">Description</h3>
                  <p className="text-gray-700 bg-gray-50 p-4 rounded-lg">
                    {trip.description}
                  </p>
                </div>
              )}
            </div>

            {/* Right Column - Driver & Booking */}
            <div className="space-y-6">
              <div>
                <h3 className="text-lg font-semibold mb-3">Conducteur</h3>
                <div className="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                  <div className="w-16 h-16 bg-eco rounded-full flex items-center justify-center text-white text-xl font-bold">
                    {trip.driver_name?.charAt(0)?.toUpperCase()}
                  </div>
                  <div className="flex-1">
                    <div className="font-medium text-lg">{trip.driver_name}</div>
                    {trip.driver_rating && (
                      <div className="flex items-center text-yellow-500 mt-1">
                        <span>⭐</span>
                        <span className="ml-1 font-medium">
                          {parseFloat(trip.driver_rating).toFixed(1)}
                        </span>
                        <span className="ml-1 text-gray-500 text-sm">
                          ({trip.driver_review_count} avis)
                        </span>
                      </div>
                    )}
                    {trip.driver_phone && (
                      <div className="text-sm text-gray-600 mt-1">
                        📞 {trip.driver_phone}
                      </div>
                    )}
                  </div>
                </div>
              </div>

              <div>
                <h3 className="text-lg font-semibold mb-3">Disponibilité</h3>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span>Places disponibles:</span>
                    <span className="font-medium">{availableSeats}/{trip.available_seats}</span>
                  </div>
                  {trip.distance_km && (
                    <div className="flex justify-between">
                      <span>Distance:</span>
                      <span className="font-medium">{trip.distance_km} km</span>
                    </div>
                  )}
                  {trip.eco_points > 0 && (
                    <div className="flex justify-between">
                      <span>Points éco:</span>
                      <span className="font-medium text-eco">+{trip.eco_points}</span>
                    </div>
                  )}
                </div>
              </div>

              {/* Booking Section */}
              {user && !isDriverCurrentUser && availableSeats > 0 && !hasUserParticipated && (
                <div className="bg-eco-light bg-opacity-10 p-6 rounded-lg">
                  <h3 className="text-lg font-semibold mb-4">Réserver ce trajet</h3>
                  
                  <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Nombre de places:
                    </label>
                    <select
                      value={seatsToBook}
                      onChange={(e) => setSeatsToBook(parseInt(e.target.value))}
                      className="input-eco"
                    >
                      {Array.from({ length: Math.min(availableSeats, 8) }, (_, i) => i + 1).map(num => (
                        <option key={num} value={num}>
                          {num} place{num > 1 ? 's' : ''}
                        </option>
                      ))}
                    </select>
                  </div>

                  <div className="mb-4 p-3 bg-white rounded border">
                    <div className="flex justify-between">
                      <span>Prix par place:</span>
                      <span>{trip.price_per_seat}€</span>
                    </div>
                    <div className="flex justify-between font-semibold">
                      <span>Total:</span>
                      <span className="text-eco">{(trip.price_per_seat * seatsToBook).toFixed(2)}€</span>
                    </div>
                  </div>

                  <button
                    onClick={handleJoinTrip}
                    disabled={joining}
                    className="w-full btn-eco disabled:opacity-50"
                  >
                    {joining ? 'Demande en cours...' : 'Demander à participer'}
                  </button>
                </div>
              )}

              {!user && (
                <div className="bg-gray-100 p-6 rounded-lg text-center">
                  <p className="text-gray-600 mb-4">
                    Connectez-vous pour réserver ce trajet
                  </p>
                  <a href="/login" className="btn-eco">
                    Se connecter
                  </a>
                </div>
              )}

              {isDriverCurrentUser && (
                <div className="bg-blue-50 p-6 rounded-lg">
                  <h4 className="font-semibold text-blue-900 mb-2">Votre trajet</h4>
                  <p className="text-blue-700 text-sm">
                    Vous êtes le conducteur de ce trajet.
                  </p>
                </div>
              )}

              {hasUserParticipated && (
                <div className="bg-green-50 p-6 rounded-lg">
                  <h4 className="font-semibold text-green-900 mb-2">Participation en cours</h4>
                  <p className="text-green-700 text-sm">
                    Vous avez déjà demandé à participer à ce trajet.
                  </p>
                </div>
              )}
            </div>
          </div>

          {/* Participants */}
          {trip.participations && trip.participations.length > 0 && (
            <div>
              <h3 className="text-lg font-semibold mb-4">
                Participants ({trip.participations.filter(p => p.status === 'confirmed').length})
              </h3>
              <div className="space-y-3">
                {trip.participations.filter(p => p.status === 'confirmed').map((participation) => (
                  <div key={participation.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div className="flex items-center space-x-3">
                      <div className="w-10 h-10 bg-eco rounded-full flex items-center justify-center text-white font-medium">
                        {participation.passenger_name?.charAt(0)?.toUpperCase()}
                      </div>
                      <div>
                        <div className="font-medium">{participation.passenger_name}</div>
                        <div className="text-sm text-gray-600">
                          {participation.seats_reserved} place{participation.seats_reserved > 1 ? 's' : ''}
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default TripDetails;