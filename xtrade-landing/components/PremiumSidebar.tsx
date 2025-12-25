
import React from 'react';

const PremiumSidebar: React.FC = () => {
  return (
    <div className="flex flex-col space-y-6">
      <div className="bg-blue-600 text-white p-8 rounded-2xl relative overflow-hidden group shadow-lg shadow-blue-200">
        <div className="relative z-10">
          <h3 className="text-xl font-bold mb-3">XTrade Premium</h3>
          <p className="text-blue-100 text-sm mb-6 leading-relaxed">
            Advanced tools for the modern trader. Real-time data and zero commission.
          </p>
          <button className="w-full py-3 bg-white text-blue-600 text-sm font-bold rounded-xl hover:bg-blue-50 transition-colors">
            Get Started
          </button>
        </div>
        
        {/* Background abstraction */}
        <div className="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl group-hover:bg-white/20 transition-all duration-700"></div>
        <div className="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12 blur-2xl"></div>
      </div>
      
      <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col items-center">
        <button className="w-full py-4 border-2 border-blue-600 text-blue-600 font-bold rounded-xl hover:bg-blue-50 transition-colors text-sm">
          Trade AAPL Stocks
        </button>
        <p className="mt-4 text-[10px] text-slate-400 text-center uppercase tracking-[0.2em] font-black">
          Pacific Standard Time
        </p>
      </div>
    </div>
  );
};

export default PremiumSidebar;
