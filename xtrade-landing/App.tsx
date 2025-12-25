
import React from 'react';
import Navbar from './components/Navbar';
import HeroSection from './components/HeroSection';
import MarketChart from './components/MarketChart';
import PlatformSection from './components/PlatformSection';
import ShareBar from './components/ShareBar';
import Footer from './components/Footer';
import { AAPL_MOCK } from './constants';

const App: React.FC = () => {
  return (
    <div className="min-h-screen flex flex-col font-sans bg-gray-50 selection:bg-blue-100 selection:text-blue-900">
      <Navbar />
      <HeroSection data={AAPL_MOCK} />
      
      <main className="flex-grow">
        <div className="max-w-5xl mx-auto px-6 md:px-12 mt-12 relative z-10 pb-10">
          <div className="space-y-12">
            <div className="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
              <MarketChart />
            </div>
            
            <div className="bg-white p-10 rounded-2xl border border-slate-200 shadow-sm">
              <div className="flex items-center space-x-3 mb-6">
                 <div className="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                 <h3 className="text-2xl font-bold text-slate-900 tracking-tight">Technical Analysis</h3>
              </div>
              <p className="text-slate-500 text-lg leading-relaxed font-medium">
                The current performance of <span className="text-blue-600 font-bold">{AAPL_MOCK.name}</span> suggests a strong consolidation phase. Technical indicators point towards a potential breakout if volume sustains above the 10-day moving average.
              </p>
              <div className="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="p-6 rounded-2xl bg-slate-50 border border-slate-100">
                  <span className="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Market Cap</span>
                  <span className="text-2xl font-extrabold text-slate-900">{AAPL_MOCK.mktCap}</span>
                </div>
                <div className="p-6 rounded-2xl bg-slate-50 border border-slate-100">
                  <span className="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">P/E Ratio (TTM)</span>
                  <span className="text-2xl font-extrabold text-slate-900">{AAPL_MOCK.peRatio}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Platform section added here */}
        <PlatformSection />
      </main>

      <Footer />
      
      {/* Social Share Bar */}
      <ShareBar />
    </div>
  );
};

export default App;
