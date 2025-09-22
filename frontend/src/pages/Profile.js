import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { userService } from '../services/api';

const Profile = () => {
  const { user, updateUser } = useAuth();
  const [isEditing, setIsEditing] = useState(false);
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    name: user?.name || '',
    phone: user?.phone || '',
    bio: user?.bio || ''
  });

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    
    try {
      const response = await userService.updateProfile(formData);
      updateUser(response.data);
      setIsEditing(false);
      alert('Profil mis à jour avec succès !');
    } catch (error) {
      console.error('Error updating profile:', error);
      alert('Erreur lors de la mise à jour du profil');
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    setFormData({
      name: user?.name || '',
      phone: user?.phone || '',
      bio: user?.bio || ''
    });
    setIsEditing(false);
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Profile Card */}
          <div className="lg:col-span-1">
            <div className="card text-center">
              <div className="w-24 h-24 bg-eco rounded-full flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
                {user?.name?.charAt(0)?.toUpperCase() || 'U'}
              </div>
              <h2 className="text-xl font-bold text-gray-900 mb-2">{user?.name}</h2>
              <p className="text-gray-600 mb-4">{user?.email}</p>
              
              <div className="grid grid-cols-2 gap-4 text-center">
                <div>
                  <div className="text-2xl font-bold text-eco">{user?.eco_credits || 0}</div>
                  <div className="text-sm text-gray-600">Crédits éco</div>
                </div>
                <div>
                  <div className="text-2xl font-bold text-eco">4.8</div>
                  <div className="text-sm text-gray-600">Note moyenne</div>
                </div>
              </div>

              <div className="mt-6 space-y-2">
                <div className="flex items-center justify-between text-sm">
                  <span className="text-gray-600">Membre depuis</span>
                  <span className="font-medium">
                    {user?.created_at ? new Date(user.created_at).getFullYear() : '2024'}
                  </span>
                </div>
                <div className="flex items-center justify-between text-sm">
                  <span className="text-gray-600">Statut</span>
                  <span className={`px-2 py-1 rounded-full text-xs ${
                    user?.email_verified 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-yellow-100 text-yellow-800'
                  }`}>
                    {user?.email_verified ? 'Vérifié' : 'En attente'}
                  </span>
                </div>
              </div>
            </div>
          </div>

          {/* Profile Details */}
          <div className="lg:col-span-2">
            <div className="card">
              <div className="flex justify-between items-center mb-6">
                <h3 className="text-xl font-bold text-gray-900">
                  Informations personnelles
                </h3>
                {!isEditing && (
                  <button
                    onClick={() => setIsEditing(true)}
                    className="btn-eco-outline"
                  >
                    ✏️ Modifier
                  </button>
                )}
              </div>

              {isEditing ? (
                <form onSubmit={handleSubmit} className="space-y-6">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Nom complet
                    </label>
                    <input
                      type="text"
                      name="name"
                      value={formData.name}
                      onChange={handleChange}
                      className="input-eco"
                      required
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Email
                    </label>
                    <input
                      type="email"
                      value={user?.email || ''}
                      className="input-eco bg-gray-100"
                      disabled
                    />
                    <p className="text-sm text-gray-500 mt-1">
                      L'email ne peut pas être modifié
                    </p>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Téléphone
                    </label>
                    <input
                      type="tel"
                      name="phone"
                      value={formData.phone}
                      onChange={handleChange}
                      className="input-eco"
                      placeholder="+33 6 12 34 56 78"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Biographie
                    </label>
                    <textarea
                      name="bio"
                      value={formData.bio}
                      onChange={handleChange}
                      rows="4"
                      className="input-eco"
                      placeholder="Parlez-nous un peu de vous..."
                    />
                  </div>

                  <div className="flex space-x-4">
                    <button
                      type="submit"
                      disabled={loading}
                      className="btn-eco disabled:opacity-50"
                    >
                      {loading ? 'Sauvegarde...' : 'Sauvegarder'}
                    </button>
                    <button
                      type="button"
                      onClick={handleCancel}
                      className="btn-eco-outline"
                    >
                      Annuler
                    </button>
                  </div>
                </form>
              ) : (
                <div className="space-y-6">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Nom complet
                    </label>
                    <div className="p-3 bg-gray-50 rounded-lg">
                      {user?.name || 'Non renseigné'}
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Email
                    </label>
                    <div className="p-3 bg-gray-50 rounded-lg">
                      {user?.email}
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Téléphone
                    </label>
                    <div className="p-3 bg-gray-50 rounded-lg">
                      {user?.phone || 'Non renseigné'}
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">
                      Biographie
                    </label>
                    <div className="p-3 bg-gray-50 rounded-lg min-h-24">
                      {user?.bio || 'Aucune biographie renseignée'}
                    </div>
                  </div>
                </div>
              )}
            </div>

            {/* Eco Credits */}
            <div className="card mt-8">
              <h3 className="text-xl font-bold text-gray-900 mb-6">
                🌱 Mes crédits écologiques
              </h3>
              
              <div className="bg-eco-light bg-opacity-10 p-6 rounded-lg mb-6">
                <div className="text-center">
                  <div className="text-4xl font-bold text-eco mb-2">
                    {user?.eco_credits || 0}
                  </div>
                  <div className="text-gray-600">Crédits disponibles</div>
                </div>
              </div>

              <div className="space-y-4">
                <h4 className="font-semibold text-gray-900">Comment gagner des crédits ?</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                  <div className="flex items-center space-x-2">
                    <span className="text-eco">⚡</span>
                    <span>Véhicule électrique: +3 points/km</span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <span className="text-eco">🔋</span>
                    <span>Véhicule hybride: +2 points/km</span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <span className="text-eco">👥</span>
                    <span>Covoiturage: +1 point/passager</span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <span className="text-eco">⭐</span>
                    <span>Bonne évaluation: +5 points</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Profile;