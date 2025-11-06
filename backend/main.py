"""
Main FastAPI application for the wrestling arena backend.
Connects the game frontend with the RL training system.
"""
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import logging
from datetime import datetime

from database import get_db_manager
from api import router

# Setup logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s",
)
logger = logging.getLogger(__name__)

# Create FastAPI app
app = FastAPI(
    title="Wrestling Arena Backend",
    description="RL Training Backend for 3D Wrestling Arena Championship Royale",
    version="1.0.0",
)

# Add CORS middleware (allow requests from game frontend)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # In production, restrict to your domain
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# Initialize database on startup
@app.on_event("startup")
async def startup_event():
    """Initialize database on startup"""
    db_manager = get_db_manager()
    db_manager.init_db()
    logger.info("✅ Database initialized")


@app.on_event("shutdown")
async def shutdown_event():
    """Cleanup on shutdown"""
    db_manager = get_db_manager()
    db_manager.close()
    logger.info("Database connection closed")


# Include API routes
app.include_router(router, prefix="/api")


# Root endpoint
@app.get("/")
async def root():
    """Welcome endpoint"""
    return {
        "message": "Wrestling Arena Backend",
        "version": "1.0.0",
        "docs": "/docs",
    }


# Health check at root level (for game frontend)
@app.get("/health")
async def health_root():
    """Health check endpoint at root level"""
    try:
        db_manager = get_db_manager()
        session = db_manager.get_session()  # Test database connection
        session.close()
        return {
            "status": "ok",
            "timestamp": datetime.utcnow().isoformat(),
            "database_ready": True,
        }
    except Exception as e:
        return {
            "status": "degraded",
            "timestamp": datetime.utcnow().isoformat(),
            "database_ready": False,
            "error": str(e),
        }


if __name__ == "__main__":
    import uvicorn
    from config import HOST, PORT, DEBUG

    uvicorn.run(
        "main:app",
        host=HOST,
        port=PORT,
        reload=DEBUG,
    )
