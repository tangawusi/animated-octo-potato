import './styles/app.scss';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { IndexPage } from './pages/index/page';

const container = document.getElementById('app');

if (container) {
    const root = createRoot(container);
    root.render(<IndexPage />);
}
