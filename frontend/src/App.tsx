import { useState, useEffect } from "react";
import reactLogo from "./assets/react.svg";
import laravelLogo from "/laravel.svg";
import "./App.css";

function App() {
  const [progress, setProgress] = useState(0);
  const [cacheStatus, setCacheStatus] = useState<string | null>(null);
  const [eventSource, setEventSource] = useState<EventSource | null>(null);
  const [isSimulationRunning, setIsSimulationRunning] = useState(false);

  // Fonction pour démarrer la simulation et la connexion SSE
  const startSimulation = (type: "random" | "realistic" = "random") => {
    if (isSimulationRunning) {
        console.log("Simulation already in progress");
      return;
    }

    setIsSimulationRunning(true);
    setProgress(0);

    // Fermer la connexion existante si elle existe
    if (eventSource) {
      eventSource.close();
    }

    // Ouvre la connexion SSE vers le backend pour folder 12
    const es = new EventSource(
      `http://localhost:8000/stream-progression?id=12&type=${type}`
    );

    es.onmessage = (event) => {
      console.log("SSE message received:", event.data);
      const percent = parseInt(event.data);
      if (!isNaN(percent)) {
        setProgress(percent);
        if (percent >= 100) {
          es.close();
          setIsSimulationRunning(false);
          setEventSource(null);
        }
      }
    };

    es.onerror = (error) => {
      console.error("SSE error:", error);
      setProgress(0);
      setIsSimulationRunning(false);
      setEventSource(null);
    };

    es.onopen = () => {
      console.log("SSE connection opened");
    };

    setEventSource(es);
  };

  const stopSimulation = () => {
    if (eventSource) {
      eventSource.close();
      setEventSource(null);
    }
    setIsSimulationRunning(false);
    setProgress(0);
  };

  // Nettoyage à l'unmount
  useEffect(() => {
    return () => {
      if (eventSource) {
        console.log("Closing SSE connection on unmount");
        eventSource.close();
      }
    };
  }, [eventSource]);

  // Fonction appelée par le nouveau bouton pour récupérer l'état dans le cache via fetch
  const fetchCacheStatus = async () => {
    try {
      console.log("Fetching cache status...");
      const response = await fetch(
        "http://localhost:8000/progress-cache?id=12"
      );

      console.log("Response status:", response.status);
      console.log("Response headers:", response.headers);

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        console.log("Error response:", errorData);
          setCacheStatus(
            `Error ${response.status}: ${
              errorData.message || "No data found in cache."
            }`
          );
        return;
      }

      const data = await response.json();
      console.log("Cache data received:", data);
      setCacheStatus(`Progress from cache: ${data.progress}%`);
    } catch (error) {
      console.error("Fetch error:", error);
      const errorMessage =
        error instanceof Error ? error.message : "Unknown error";
      setCacheStatus(
        `Error retrieving cache: ${errorMessage}`
      );
    }
  };

  return (
    <>
      <div>
        <a href="http://localhost:8000" target="_blank">
          <img src={laravelLogo} className="logo" alt="Vite logo" />
        </a>
        <a href="https://react.dev" target="_blank">
          <img src={reactLogo} className="logo react" alt="React logo" />
        </a>
      </div>
      <h1>
        Test Server Events <br /> & Progression Stream{" "}
      </h1>
      <div className="card">
        {/* Barre de progression - affichée seulement si simulation en cours */}
        {isSimulationRunning && (
          <div
            style={{
              width: "200px",
              height: "24px",
              border: "1px solid #aaa",
              position: "relative",
              marginBottom: "10px",
              margin: "0 auto 10px auto",
            }}
          >
            <div
              id="progress-bar"
              style={{
                height: "100%",
                width: progress + "%",
                background: "linear-gradient(90deg,#42d392 0%, #647eff 100%)",
                transition: "width 0.25s",
              }}
            />
            <span
              style={{
                position: "absolute",
                left: "50%",
                top: "0",
                transform: "translateX(-50%)",
                color: "inherit",
              }}
            >
              {progress}%
            </span>
          </div>
        )}
        {/* Boutons centrés sur une même ligne */}
        <div
          style={{
            display: "flex",
            justifyContent: "center",
            gap: "10px",
            marginBottom: "10px",
            flexWrap: "wrap",
          }}
        >
          <button
            onClick={
              isSimulationRunning
                ? stopSimulation
                : () => startSimulation("realistic")
            }
            style={{
              backgroundColor: isSimulationRunning ? "#ff6b6b" : "#647eff",
              color: "white",
              border: "none",
              padding: "10px 20px",
              borderRadius: "5px",
              cursor: "pointer",
            }}
          >
            {isSimulationRunning ? "Stop" : "Realistic Progress"}
          </button>

          <button
            onClick={fetchCacheStatus}
            style={{
              backgroundColor: "#8b5cf6",
              color: "white",
              border: "none",
              padding: "10px 20px",
              borderRadius: "5px",
              cursor: "pointer",
            }}
          >
            Show Cache Status
          </button>
        </div>
        {/* Indicateur d'état de la simulation */}
        <div
          style={{ marginTop: "28px", fontSize: "14px", textAlign: "center" }}
        >
          <span
            style={{
              color: isSimulationRunning ? "#42d392" : "#666",
              fontWeight: "bold",
            }}
          >
              {isSimulationRunning
                ? "🟢 Simulation in progress..."
                : "⚪ Click 'Realistic Progress' to start"}
          </span>
        </div>
        {/* Affichage du résultat de la route /progress-cache */}
        {cacheStatus && <p style={{ marginTop: "10px" }}>{cacheStatus}</p>}
      </div>
    </>
  );
}

export default App;

