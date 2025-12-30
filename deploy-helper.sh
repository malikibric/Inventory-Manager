#!/bin/bash

echo "=========================================="
echo "Render Deployment Helper"
echo "=========================================="
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "⚠️  .env file not found!"
    echo "Creating .env from .env.example..."
    cp .env.example .env
    echo "✓ .env created. Please edit it with your credentials."
    exit 1
fi

echo "✓ .env file found"
echo ""

# Test database connection
echo "Testing database connection..."
php test-connection.php

if [ $? -eq 0 ]; then
    echo ""
    echo "=========================================="
    echo "Next Steps:"
    echo "=========================================="
    echo ""
    echo "1. Import database schema:"
    echo "   psql \$DATABASE_URL < database_postgresql.sql"
    echo ""
    echo "2. Generate JWT secret:"
    echo "   openssl rand -base64 64"
    echo ""
    echo "3. Update .env with JWT_SECRET"
    echo ""
    echo "4. Push to GitHub:"
    echo "   git add ."
    echo "   git commit -m 'Ready for Render'"
    echo "   git push origin main"
    echo ""
    echo "5. Deploy on Render:"
    echo "   - Go to dashboard.render.com"
    echo "   - New + → Blueprint"
    echo "   - Connect repository"
    echo "   - Apply"
    echo ""
else
    echo ""
    echo "❌ Database connection failed!"
    echo "Please check your DATABASE_URL in .env file"
fi
