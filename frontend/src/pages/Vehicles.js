import React from 'react';

const Vehicles = () => (
  <div className="min-h-screen bg-gray-50 py-8">
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div className="card text-center">
        <div className="text-6xl mb-4">🚙</div>
        <h1 className="text-3xl font-bold text-gray-900 mb-4">Mes véhicules</h1>
        <p className="text-gray-600 mb-6">Fonctionnalité en cours de développement</p>
        <div className="text-sm text-gray-500">
          Cette page permettra de gérer vos véhicules : ajouter, modifier, supprimer,
          avec informations sur la consommation, type de carburant, etc.
        </div>
      </div>
    </div>
  </div>
);

export default Vehicles;