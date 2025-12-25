
import React from 'react';

const Footer: React.FC = () => {
  const sections = [
    {
      title: 'XTrade Products',
      links: ['Futures', 'Options', 'Stocks & Fractional Shares', 'ETFs', 'Buy & Sell Crypto', 'XTrade Advisors']
    },
    {
      title: 'About XTrade',
      links: ['Investor Relations', 'Careers', 'BrokerCheck', 'Pricing', 'Blog', 'Script Editor']
    },
    {
      title: 'FAQs',
      links: ['Account & Login', 'Retirement', 'Documents & Taxes']
    },
    {
      title: 'Terms & Conditions',
      links: ['Privacy Policy', 'Business Continuity Plan', 'Disclosures', 'Data Disclaimer']
    },
    {
      title: 'Cookie',
      links: ['Cookie Setting']
    }
  ];

  return (
    <footer className="bg-white border-t border-gray-100 pt-16 pb-8 px-4 md:px-8">
      <div className="max-w-7xl mx-auto">
        <div className="mb-12">
          <p className="text-gray-500 text-sm leading-relaxed max-w-4xl">
            XTrade offers Apple Inc. stock information, including NYSE: AAPL real-time market quotes, financial reports, professional analyst ratings, in-depth charts, corporate actions, AAPL stock news, and many more online research tools to help you make informed decisions. 
            <a href="#" className="text-blue-600 hover:underline mx-1">Trade stocks</a> 
            for 0 commission on the web version for easy and convenient access, or download the XTrade app and trade on the go. You can practice and explore trading AAPL stock methods without spending real money on the virtual 
            <a href="#" className="text-blue-600 hover:underline mx-1">paper trading platform</a>.
          </p>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 mb-16">
          {sections.map((section) => (
            <div key={section.title} className="flex flex-col space-y-4">
              <h4 className="text-sm font-bold text-gray-900">{section.title}</h4>
              <ul className="flex flex-col space-y-2">
                {section.links.map((link) => (
                  <li key={link}>
                    <a href="#" className="text-xs text-gray-500 hover:text-blue-600 transition-colors">{link}</a>
                  </li>
                ))}
              </ul>
            </div>
          ))}
          
          <div className="flex flex-col space-y-4">
            <h4 className="text-sm font-bold text-gray-900">Contact us</h4>
            <div className="text-xs text-gray-500 space-y-2">
              <p>support@xtrade.com</p>
              <p>+1 (888) 555-0199</p>
            </div>
            <div className="pt-4">
              <h4 className="text-sm font-bold text-gray-900 mb-4">Follow us on</h4>
              <div className="flex space-x-4">
                <SocialIcon path="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1V12h3l-.5 3H13v6.8c4.56-.93 8-4.96 8-9.8z" />
                <SocialIcon path="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                <SocialIcon path="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.84 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                <SocialIcon path="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
              </div>
            </div>
          </div>
        </div>
        
        <div className="flex flex-col md:flex-row justify-between items-center text-[10px] text-gray-400 font-bold tracking-widest uppercase">
          <p>© 2024 XTrade Financial Inc. All rights reserved.</p>
          <div className="flex space-x-6 mt-4 md:mt-0">
            <a href="#" className="hover:text-blue-600 transition-colors">Privacy</a>
            <a href="#" className="hover:text-blue-600 transition-colors">Terms</a>
            <a href="#" className="hover:text-blue-600 transition-colors">Support</a>
          </div>
        </div>
      </div>
    </footer>
  );
};

const SocialIcon: React.FC<{ path: string }> = ({ path }) => (
  <a href="#" className="text-gray-400 hover:text-blue-600 transition-colors">
    <svg className="w-5 h-5 fill-current" viewBox="0 0 24 24">
      <path d={path} />
    </svg>
  </a>
);

export default Footer;
