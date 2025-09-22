import React, { useState, useEffect } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import { tripService } from '../services/api';

const SearchTrips = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const [trips, setTrips] = useState([]);
  const [loading, setLoading] = useState(false);
  const [pagination, setPagination] = useState({});
  const [searchForm, setSearchForm] = useState({
    from: searchParams.get('from') || '',
    to: searchParams.get('to') || '',
    date: searchParams.get('date') || '',
    seats: searchParams.get('seats') || 1
  });

  useEffect(() => {
    searchTrips();
  }, [searchParams]);

  const searchTrips = async (page = 1) => {
    setLoading(true);
    try {
      const params = {
        from: searchForm.from,
        to: searchForm.to,
        date: searchForm.date,
        seats: searchForm.seats,
        page: page,
        limit: 10
      };

      const response = await tripService.searchTrips(params);
      setTrips(response.data.trips);
      setPagination(response.data.pagination);
    } catch (error) {
      console.error('Error searching trips:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSearchChange = (e) => {
    setSearchForm({
      ...searchForm,
      [e.target.name]: e.target.value
    });
  };

  const handleSearch = (e) => {
    e.preventDefault();
    const newSearchParams = new URLSearchParams();
    
    Object.entries(searchForm).forEach(([key, value]) => {
      if (value) {
        newSearchParams.append(key, value);
      }
    });
    
    setSearchParams(newSearchParams);
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      weekday: 'short',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const formatDuration = (departureTime, arrivalTime) => {
    if (!arrivalTime) return '';
    
    const departure = new Date(departureTime);
    const arrival = new Date(arrivalTime);
    const diffMs = arrival - departure;
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    
    return `${diffHours}h${diffMinutes > 0 ? diffMinutes : ''}`;
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

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Search Form */}
        <div className="card mb-8">
          <h1 className="text-2xl font-bold text-gray-900 mb-6">
            Rechercher un trajet
          </h1>
          
          <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Départ
              </label>
              <input
                type="text"
                name="from"
                value={searchForm.from}
                onChange={handleSearchChange}
                placeholder="Ville de départ"
                className="input-eco"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Arrivée
              </label>
              <input
                type="text"
                name="to"
                value={searchForm.to}
                onChange={handleSearchChange}
                placeholder="Ville d'arrivée"
                className="input-eco"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Date
              </label>
              <input
                type="date"
                name="date"
                value={searchForm.date}
                onChange={handleSearchChange}
                className="input-eco"
                min={new Date().toISOString().split('T')[0]}
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Passagers
              </label>
              <select
                name="seats"
                value={searchForm.seats}
                onChange={handleSearchChange}
                className="input-eco"
              >
                {[1, 2, 3, 4, 5, 6, 7, 8].map(num => (
                  <option key={num} value={num}>{num}</option>
                ))}
              </select>
            </div>
            
            <div className="flex items-end">
              <button
                type="submit"
                disabled={loading}
                className="w-full btn-eco"
              >
                {loading ? '🔍 Recherche...' : '🔍 Rechercher'}
              </button>
            </div>
          </form>
        </div>

        {/* Results */}
        <div className="space-y-6">
          {loading ? (
            <div className="text-center py-12">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-eco mx-auto"></div>
              <p className="mt-4 text-gray-600">Recherche en cours...</p>
            </div>
          ) : trips.length === 0 ? (
            <div className="text-center py-12">
              <div className="text-6xl mb-4">🔍</div>
              <h3 className="text-lg font-medium text-gray-900 mb-2">
                Aucun trajet trouvé
              </h3>
              <p className="text-gray-600">
                Essayez de modifier vos critères de recherche
              </p>
            </div>
          ) : (
            <>
              <div className="flex justify-between items-center">
                <h2 className="text-xl font-semibold text-gray-900">
                  {pagination.total} trajet{pagination.total > 1 ? 's' : ''} trouvé{pagination.total > 1 ? 's' : ''}
                </h2>
                <div className="text-sm text-gray-600">
                  Page {pagination.current_page} sur {pagination.last_page}
                </div>
              </div>

              {trips.map((trip) => (
                <div key={trip.id} className="card hover:shadow-xl transition-shadow">
                  <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    {/* Trip Info */}
                    <div className="flex-1">
                      <div className="flex items-center space-x-4 mb-3">
                        <div className="flex-shrink-0">
                          <div className="w-12 h-12 bg-eco rounded-full flex items-center justify-center text-white font-bold">
                            {trip.driver_name?.charAt(0)?.toUpperCase()}
                          </div>
                        </div>
                        <div className="flex-1">
                          <div className="flex items-center space-x-2 mb-1">
                            <h3 className="font-semibold text-lg">
                              {trip.departure_city} → {trip.arrival_city}
                            </h3>
                            {trip.driver_rating && (
                              <div className="flex items-center text-yellow-500">
                                <span className="text-sm">⭐</span>
                                <span className="text-sm font-medium ml-1">
                                  {parseFloat(trip.driver_rating).toFixed(1)}
                                </span>
                              </div>
                            )}
                          </div>
                          <p className="text-gray-600 text-sm">
                            Conducteur: {trip.driver_name}
                          </p>
                        </div>
                      </div>

                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                          <div className="text-sm text-gray-500">Départ</div>
                          <div className="font-medium">{trip.departure_address}</div>
                          <div className="text-sm text-eco">
                            {formatDate(trip.departure_time)}
                          </div>
                        </div>
                        <div>
                          <div className="text-sm text-gray-500">Arrivée</div>
                          <div className="font-medium">{trip.arrival_address}</div>
                          {trip.arrival_time && (
                            <div className="text-sm text-gray-600">
                              Durée: {formatDuration(trip.departure_time, trip.arrival_time)}
                            </div>
                          )}
                        </div>
                      </div>

                      <div className="flex items-center space-x-6 text-sm text-gray-600">
                        <div className="flex items-center space-x-1">
                          <span>{getFuelIcon(trip.fuel_type)}</span>
                          <span>{trip.make} {trip.model} ({trip.year})</span>
                        </div>
                        <div className="flex items-center space-x-1">
                          <span>👥</span>
                          <span>{trip.available_seats} place{trip.available_seats > 1 ? 's' : ''} disponible{trip.available_seats > 1 ? 's' : ''}</span>
                        </div>
                        {trip.distance_km && (
                          <div className="flex items-center space-x-1">
                            <span>📍</span>
                            <span>{trip.distance_km} km</span>
                          </div>
                        )}
                        {trip.eco_points > 0 && (
                          <div className="flex items-center space-x-1">
                            <span>🌱</span>
                            <span>+{trip.eco_points} points éco</span>
                          </div>
                        )}
                      </div>

                      {trip.description && (
                        <div className="mt-3 text-sm text-gray-600 line-clamp-2">
                          {trip.description}
                        </div>
                      )}
                    </div>

                    {/* Price and Action */}
                    <div className="mt-4 lg:mt-0 lg:ml-6 flex-shrink-0 text-center lg:text-right">
                      <div className="text-2xl font-bold text-eco mb-2">
                        {trip.price_per_seat}€
                      </div>
                      <div className="text-sm text-gray-500 mb-3">par place</div>
                      <Link
                        to={`/trips/${trip.id}`}
                        className="btn-eco w-full lg:w-auto"
                      >
                        Voir le trajet
                      </Link>
                    </div>
                  </div>
                </div>
              ))}

              {/* Pagination */}
              {pagination.last_page > 1 && (
                <div className="flex justify-center space-x-2 mt-8">
                  <button
                    onClick={() => searchTrips(pagination.current_page - 1)}
                    disabled={pagination.current_page <= 1}
                    className="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                  >
                    Précédent
                  </button>
                  
                  {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map(page => (
                    <button
                      key={page}
                      onClick={() => searchTrips(page)}
                      className={`px-4 py-2 border rounded-lg ${
                        page === pagination.current_page
                          ? 'bg-eco text-white border-eco'
                          : 'border-gray-300 hover:bg-gray-50'
                      }`}
                    >
                      {page}
                    </button>
                  ))}
                  
                  <button
                    onClick={() => searchTrips(pagination.current_page + 1)}
                    disabled={pagination.current_page >= pagination.last_page}
                    className="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                  >
                    Suivant
                  </button>
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default SearchTrips;