#!/bin/bash

# EcoRide Application Test Script
echo "🌱 Testing EcoRide Application"

# Check Docker setup
echo "📋 Checking Docker setup..."
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed"
    exit 1
fi

echo "✅ Docker is available"

# Validate Docker Compose configuration
echo "📋 Validating Docker Compose configuration..."
if docker compose config --quiet; then
    echo "✅ Docker Compose configuration is valid"
else
    echo "❌ Docker Compose configuration has errors"
    exit 1
fi

# Check PHP syntax
echo "📋 Checking PHP syntax..."
cd backend

if ! command -v php &> /dev/null; then
    echo "⚠️ PHP not available for local syntax check (will work in Docker)"
else
    echo "Checking main entry point..."
    if php -l public/index.php > /dev/null 2>&1; then
        echo "✅ Main entry point syntax is valid"
    else
        echo "❌ PHP syntax errors found in main entry point"
        exit 1
    fi

    echo "Checking source files..."
    syntax_errors=0
    for file in $(find src -name "*.php"); do
        if ! php -l "$file" > /dev/null 2>&1; then
            echo "❌ Syntax error in $file"
            syntax_errors=$((syntax_errors + 1))
        fi
    done

    if [ $syntax_errors -eq 0 ]; then
        echo "✅ All PHP source files have valid syntax"
    else
        echo "❌ Found $syntax_errors PHP syntax errors"
        exit 1
    fi
fi

cd ..

# Check frontend package.json
echo "📋 Checking frontend configuration..."
if [ -f "frontend/package.json" ]; then
    echo "✅ Frontend package.json exists"
    
    if command -v node &> /dev/null; then
        cd frontend
        if node -e "JSON.parse(require('fs').readFileSync('package.json', 'utf8'))" 2>/dev/null; then
            echo "✅ Frontend package.json is valid JSON"
        else
            echo "❌ Frontend package.json is invalid JSON"
            exit 1
        fi
        cd ..
    else
        echo "⚠️ Node.js not available for local validation (will work in Docker)"
    fi
else
    echo "❌ Frontend package.json not found"
    exit 1
fi

# Check database schema
echo "📋 Checking database schema..."
if [ -f "database/init.sql" ]; then
    echo "✅ Database initialization script exists"
else
    echo "❌ Database initialization script not found"
    exit 1
fi

# Check environment files
echo "📋 Checking environment configuration..."
if [ -f "backend/.env" ]; then
    echo "✅ Backend environment file exists"
else
    echo "⚠️ Backend environment file not found (will use defaults)"
fi

echo ""
echo "🎉 All tests passed! EcoRide is ready to launch."
echo ""
echo "To start the application:"
echo "  docker compose up -d"
echo ""
echo "Access points:"
echo "  🌐 Frontend: http://localhost:3000"
echo "  🔧 Backend API: http://localhost:8080/api"
echo "  🗄️ Database: localhost:3306"
echo ""
echo "Test accounts:"
echo "  📧 admin@ecoride.com / password"
echo "  🚗 driver@ecoride.com / password"
echo "  🎒 passenger@ecoride.com / password"