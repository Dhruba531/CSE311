
import React from 'react';
import { StockData } from '../types';

interface HeroSectionProps {
  data: StockData;
}

const HeroSection: React.FC<HeroSectionProps> = ({ data }) => {
  return (
    <div className="bg-white text-slate-900 pt-10 pb-16 px-6 md:px-12 border-b border-slate-100">
      <div className="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 items-end">
        {/* Main Price Info */}
        <div className="space-y-4">
          <div className="flex flex-col">
            <h2 className="text-7xl md:text-9xl font-light tracking-tighter leading-none text-slate-900">{data.symbol}</h2>
            <div className="mt-4 text-xl font-medium text-slate-500 flex items-center">
              {data.name} 
              <span className="ml-3 px-2 py-0.5 bg-slate-100 rounded text-xs font-bold uppercase tracking-widest border border-slate-200 text-slate-600">
                {data.exchange}
              </span>
            </div>
          </div>
          
          <div className="flex flex-col pt-2">
            <div className="text-7xl md:text-8xl font-black text-[#FF5533]">
              {data.price.toFixed(2)}
            </div>
            <div className="mt-4 flex items-center space-x-3 text-2xl font-bold">
              <span className="text-slate-400">139.09</span>
              <span className="text-green-600 bg-green-50 px-3 py-1 rounded-full text-lg">
                +{data.change.toFixed(2)} (+{data.changePercent}%)
              </span>
            </div>
            <div className="mt-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">
              {data.status}: {data.timestamp} EST
            </div>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-x-10 gap-y-8 lg:ml-auto border-t lg:border-t-0 lg:border-l border-slate-100 pt-10 lg:pt-0 lg:pl-10">
          <StatItem label="OPEN" value={data.open} />
          <StatItem label="PREV CLOSE" value={data.prevClose} />
          <StatItem label="HIGH" value={data.high} />
          <StatItem label="LOW" value={data.low} />
          <StatItem label="VOLUME" value={data.volume} />
          <StatItem label="TURNOVER" value={data.turnover} />
          <StatItem label="52 WK HIGH" value={data.fiftyTwoWkHigh} />
          <StatItem label="52 WK LOW" value={data.fiftyTwoWkLow} />
        </div>
      </div>
    </div>
  );
};

const StatItem: React.FC<{ label: string; value: string }> = ({ label, value }) => (
  <div className="flex flex-col group cursor-default">
    <span className="text-[10px] font-black text-slate-400 mb-1 group-hover:text-slate-900 transition-colors tracking-widest uppercase">{label}</span>
    <span className="text-base font-bold tabular-nums text-slate-800">{value}</span>
  </div>
);

export default HeroSection;
