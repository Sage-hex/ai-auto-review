import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App.jsx'
import './index.css'

// Remove the React.StrictMode in development to avoid double API calls
ReactDOM.createRoot(document.getElementById('root')).render(
  <App />
)
