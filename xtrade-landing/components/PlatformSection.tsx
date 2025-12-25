
import React, { useState } from 'react';

const PlatformSection: React.FC = () => {
  const topPlatforms = [
    {
      title: 'Trader Workstation',
      description: 'Our flagship platform designed for active traders and investors who require power and flexibility.',
      // High-end monitor terminal setup
      image: 'https://images.unsplash.com/photo-1611974714024-4627450700c2?q=80&w=1000&auto=format&fit=crop',
    },
    {
      title: 'XTrade Desktop',
      description: 'Our newest desktop trading platform is best suited to clients who appreciate a more streamlined interface.',
      // Dark mode laptop interface
      image: 'https://images.unsplash.com/photo-1590283603385-17ffb3a7f29f?q=80&w=1000&auto=format&fit=crop',
    },
    {
      title: 'XTrade Mobile',
      description: 'Trade on the go with our powerful mobile app. Available for iOS and Android.',
      // Dual mobile device showcase
      image: 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?q=80&w=1000&auto=format&fit=crop',
    }
  ];

  const bottomShowcase = [
    {
      image: 'https://images.unsplash.com/photo-1556656793-062ff987b50d?q=80&w=800&auto=format&fit=crop',
      alt: 'Mobile Performance View'
    },
    {
      image: 'https://images.unsplash.com/photo-1581291518064-9ed2a3e39545?q=80&w=1000&auto=format&fit=crop',
      alt: 'Tablet Analytics Dashboard'
    },
    {
      image: 'https://images.unsplash.com/photo-1547082299-de196ea013d6?q=80&w=1000&auto=format&fit=crop',
      alt: 'Multi-device Trading Station'
    }
  ];

  return (
    <section className="py-24 bg-white border-t border-slate-100 relative overflow-hidden">
      <div className="max-w-7xl mx-auto px-6 md:px-12">
        {/* Top Row: Platforms with Titles and Descriptions */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-16 text-center mb-32">
          {topPlatforms.map((platform, idx) => (
            <div key={idx} className="flex flex-col items-center group">
              <div className="mb-12 w-full h-72 flex items-center justify-center transition-transform duration-700 group-hover:scale-105">
                <SafeImage 
                  src={platform.image} 
                  alt={platform.title} 
                  className="max-h-full max-w-full object-contain drop-shadow-2xl"
                />
              </div>
              
              <div className="flex items-center justify-center space-x-2 mb-4 group cursor-pointer">
                <h3 className="text-2xl font-bold text-[#C8102E] leading-tight transition-colors group-hover:text-red-700">
                  {platform.title}
                </h3>
                <div className="bg-[#C8102E] rounded-full p-1.5 flex items-center justify-center w-7 h-7 shadow-sm transition-transform group-hover:scale-110">
                   <svg className="w-4 h-4 text-white fill-current" viewBox="0 0 24 24">
                     <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" />
                   </svg>
                </div>
              </div>
              
              <p className="text-slate-500 text-base leading-relaxed max-w-xs mx-auto font-medium">
                {platform.description}
              </p>
            </div>
          ))}
        </div>

        {/* Bottom Row: Detailed Showcase Row (The additional 3 specific visuals) */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-12 items-end">
           {bottomShowcase.map((item, idx) => (
             <div key={idx} className="flex flex-col items-center group">
               <div className={`relative w-full overflow-hidden transition-all duration-700 rounded-3xl ${idx === 0 ? 'max-w-[240px] shadow-2xl border-4 border-slate-900' : 'shadow-xl border border-slate-100 hover:shadow-2xl'}`}>
                 <SafeImage 
                   src={item.image} 
                   alt={item.alt} 
                   className={`w-full h-auto object-cover ${idx === 0 ? 'aspect-[9/19]' : idx === 1 ? 'aspect-[4/3]' : 'aspect-video'}`} 
                 />
                 
                 {/* Special overlay for the desktop+mobile combo image (last one) */}
                 {idx === 2 && (
                   <div className="absolute -bottom-4 -right-4 w-28 h-56 hidden xl:block shadow-2xl border-4 border-slate-900 rounded-2xl overflow-hidden z-20 transition-transform group-hover:translate-x-2 group-hover:-translate-y-2">
                     <SafeImage 
                        src="https://images.unsplash.com/photo-1616348436168-de43ad0db179?q=80&w=400&auto=format&fit=crop"
                        alt="Mobile device overlay"
                        className="w-full h-full object-cover"
                     />
                   </div>
                 )}
               </div>
             </div>
           ))}
        </div>
      </div>
      
      {/* Scroll to Top UI element visible in bottom right of reference */}
      <button 
        onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
        className="fixed bottom-10 right-10 bg-[#F3F4F6] hover:bg-slate-200 p-3 rounded shadow-md transition-all z-40 border border-slate-200 group active:scale-90"
        aria-label="Back to top"
      >
        <svg className="w-6 h-6 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2.5">
          <path strokeLinecap="round" strokeLinejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
      </button>
    </section>
  );
};

const SafeImage: React.FC<{ src: string; alt: string; className?: string }> = ({ src, alt, className }) => {
  const [error, setError] = useState(false);
  const [loading, setLoading] = useState(true);

  if (error) {
    return (
      <div className={`${className} bg-slate-50 flex flex-col items-center justify-center p-6 text-center`}>
        <svg className="w-12 h-12 text-slate-100 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span className="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em]">{alt}</span>
      </div>
    );
  }

  return (
    <div className="relative w-full h-full overflow-hidden">
      {loading && (
        <div className="absolute inset-0 flex items-center justify-center bg-slate-50 z-10">
          <div className="w-6 h-6 border-2 border-slate-200 border-t-[#C8102E] rounded-full animate-spin"></div>
        </div>
      )}
      <img 
        src={src} 
        alt={alt} 
        className={`${className} ${loading ? 'opacity-0' : 'opacity-100'} transition-opacity duration-700`} 
        onError={() => setError(true)}
        onLoad={() => setLoading(false)}
        loading="lazy"
      />
    </div>
  );
};

export default PlatformSection;
