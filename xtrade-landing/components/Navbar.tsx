
import React from 'react';

const Navbar: React.FC = () => {
  return (
    <nav className="bg-white text-slate-900 px-6 md:px-12 py-5 flex items-center justify-between sticky top-0 z-50 shadow-sm border-b border-slate-100">
      <div className="flex items-center space-x-10">
        <h1 className="text-3xl font-extrabold tracking-tight text-blue-600">XTrade</h1>
        <div className="hidden md:flex space-x-8 text-sm font-semibold tracking-wide text-slate-600">
          <a href="#" className="hover:text-blue-600 transition-colors">Dashboard</a>
          <a href="#" className="hover:text-blue-600 transition-colors">Portfolio</a>
          <a href="#" className="hover:text-blue-600 transition-colors">Trade</a>
        </div>
      </div>
      
      <div className="flex items-center space-x-4">
        <button className="px-6 py-2.5 text-sm font-bold text-slate-600 hover:text-blue-600 hover:bg-slate-50 rounded-full transition-all">
          Login
        </button>
        <button className="px-8 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-full hover:bg-blue-700 transition-all shadow-md active:scale-95">
          Sign Up
        </button>
      </div>
    </nav>
  );
};

export default Navbar;
