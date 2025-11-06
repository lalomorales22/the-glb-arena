"""
Database connection and session management
"""
from sqlalchemy import create_engine, event
from sqlalchemy.orm import sessionmaker, Session
from contextlib import contextmanager
import sqlite3

from config import DATABASE_URL, DB_PATH
from database.models import Base


class DatabaseManager:
    """Manages database connections and sessions"""

    def __init__(self, db_url: str = DATABASE_URL):
        # Create engine with SQLite optimizations
        self.engine = create_engine(
            db_url,
            connect_args={"check_same_thread": False},
            echo=False,
            pool_pre_ping=True,
        )

        # Enable foreign key constraints
        @event.listens_for(self.engine, "connect")
        def set_sqlite_pragma(dbapi_conn, connection_record):
            cursor = dbapi_conn.cursor()
            cursor.execute("PRAGMA foreign_keys=ON")
            cursor.execute("PRAGMA journal_mode=WAL")  # Write-ahead logging for concurrency
            cursor.close()

        # Create session factory
        self.SessionLocal = sessionmaker(
            autocommit=False,
            autoflush=False,
            bind=self.engine,
        )

        # Initialize database
        self._initialized = False

    def init_db(self):
        """Create all tables"""
        if not self._initialized:
            Base.metadata.create_all(bind=self.engine)
            self._initialized = True

    def get_session(self) -> Session:
        """Get a new database session"""
        return self.SessionLocal()

    @contextmanager
    def session_scope(self):
        """Context manager for database sessions"""
        session = self.SessionLocal()
        try:
            yield session
            session.commit()
        except Exception:
            session.rollback()
            raise
        finally:
            session.close()

    def close(self):
        """Close all connections"""
        self.engine.dispose()


# Global database manager instance
_db_manager = None


def get_db_manager() -> DatabaseManager:
    """Get or create the global database manager"""
    global _db_manager
    if _db_manager is None:
        _db_manager = DatabaseManager()
        _db_manager.init_db()
    return _db_manager


def get_db() -> Session:
    """Dependency injection for FastAPI endpoints"""
    db = get_db_manager().get_session()
    try:
        yield db
    finally:
        db.close()
