<?php
session_start();
require 'config.php';

// Auth Check
if (isset($_SESSION['user_id'])) {
    header("Location: portfolio.php");
    exit;
} else {
    // Public Landing Page (Logged Out View)
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>XTrade - Professional Trading Terminal</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { font-family: 'Inter', sans-serif; background: #fafafa; color: #18181b; }
            .hero-bg {
                background: radial-gradient(circle at 50% 0%, #f4f4f5 0%, #ffffff 100%);
            }
            .glass-nav {
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(0,0,0,0.05);
            }
            .btn-primary {
                background: #000; color: #fff; border-radius: 999px; padding: 12px 24px; font-weight: 600; transition: all 0.2s;
            }
            .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
            .feature-card {
                background: white; border: 1px solid #e4e4e7; border-radius: 1.5rem; padding: 2rem;
                transition: all 0.3s;
            }
            .feature-card:hover { border-color: #000; transform: translateY(-2px); }
        </style>
    </head>
    <body class="hero-bg min-h-screen flex flex-col">
        <!-- Nav -->
        <nav class="glass-nav fixed w-full z-50">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <div class="text-2xl font-black tracking-tight flex items-center gap-2">
                    <span class="w-8 h-8 bg-black rounded-lg flex items-center justify-center text-white text-sm">X</span>
                    XTrade
                </div>
                <div class="flex items-center gap-4">
                    <a href="login.php" class="text-sm font-semibold hover:text-black text-zinc-600 transition-colors">Log In</a>
                    <a href="register.php" class="btn-primary text-sm">Get Started</a>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <main class="flex-1 flex flex-col items-center justify-center pt-32 pb-20 px-6 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-zinc-100 border border-zinc-200 mb-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-600">Live Markets Open</span>
            </div>
            
            <h1 class="text-5xl md:text-7xl font-black tracking-tighter mb-6 max-w-4xl leading-[1.1] animate-in fade-in slide-in-from-bottom-8 duration-700 delay-100">
                Master the Markets with <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-zinc-900 to-zinc-500">Precision & Speed.</span>
            </h1>
            
            <p class="text-lg md:text-xl text-zinc-500 max-w-2xl mb-10 leading-relaxed animate-in fade-in slide-in-from-bottom-8 duration-700 delay-200">
                Experience institutional-grade trading tools, real-time data, and zero-latency execution. 
                Built for the modern investor.
            </p>
            
            <div class="flex flex-col sm:flex-row items-center gap-4 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-300">
                <a href="register.php" class="bg-black text-white h-14 px-8 rounded-full font-bold flex items-center justify-center hover:scale-105 transition-transform">
                    Open Free Account
                </a>
                <a href="stock_details.php?ticker=AAPL" class="h-14 px-8 rounded-full font-bold flex items-center justify-center border border-zinc-200 hover:border-black hover:bg-white transition-all">
                    View Live Demo
                </a>
            </div>

            <!-- Webull-style Live Cards Section -->
            <div class="mt-16 w-full max-w-6xl mx-auto relative h-[500px] perspective-1000">
                
                <!-- Background Glow -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-blue-500/20 blur-[100px] rounded-full"></div>

                <!-- Card 1: Stocks (Left, Behind) -->
                <div class="absolute top-10 left-0 md:left-10 w-[300px] md:w-[380px] bg-white rounded-2xl shadow-xl border border-zinc-100 p-5 transform -rotate-6 scale-90 opacity-90 z-10 transition-all hover:z-50 hover:scale-100 hover:rotate-0 duration-500">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <div class="font-bold text-lg">AAPL</div>
                            <div class="text-xs text-zinc-500">Apple Inc.</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-green-500">+1.24%</div>
                            <div class="text-xs font-bold">$185.92</div>
                        </div>
                    </div>
                    <div class="h-[150px]">
                        <canvas id="chartStocks"></canvas>
                    </div>
                </div>

                <!-- Card 2: ETFs (Right, Behind) -->
                <div class="absolute top-10 right-0 md:right-10 w-[300px] md:w-[380px] bg-white rounded-2xl shadow-xl border border-zinc-100 p-5 transform rotate-6 scale-90 opacity-90 z-10 transition-all hover:z-50 hover:scale-100 hover:rotate-0 duration-500">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <div class="font-bold text-lg">SPY</div>
                            <div class="text-xs text-zinc-500">S&P 500 ETF</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-green-500">+0.85%</div>
                            <div class="text-xs font-bold">$478.10</div>
                        </div>
                    </div>
                    <div class="h-[150px]">
                        <canvas id="chartEtfs"></canvas>
                    </div>
                </div>

                <!-- Card 3: Crypto (Center, Front) -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[340px] md:w-[420px] bg-white rounded-3xl shadow-2xl border border-zinc-200 p-6 z-30 transform hover:scale-105 transition-transform duration-500">
                    <div class="flex items-center gap-4 mb-6 border-b border-zinc-50 pb-4">
                        <div class="w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center text-2xl">₿</div>
                        <div class="flex-1">
                            <div class="font-black text-2xl tracking-tight">Bitcoin</div>
                            <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Live &bull; BTC/USD</div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black" id="btc-price">$42,150.00</div>
                            <div class="text-xs font-bold text-green-500 bg-green-50 px-2 py-1 rounded-full inline-block">+2.4%</div>
                        </div>
                    </div>
                    
                    <div class="h-[200px] w-full">
                        <canvas id="chartCrypto"></canvas>
                    </div>

                    <div class="mt-6 flex gap-2">
                        <button class="flex-1 py-3 bg-black text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-zinc-800 transition-colors">Trade Now</button>
                        <button class="flex-1 py-3 bg-zinc-100 text-zinc-900 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-zinc-200 transition-colors">Details</button>
                    </div>
                </div>

            </div>
        </main>

        <!-- Google Finance-style Light Dashboard -->
        <section class="py-20 bg-white text-zinc-900 relative z-20 top-20"> 
            <div class="max-w-7xl mx-auto px-6">
                
                <!-- Header / Search -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 border-b border-zinc-200 pb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-3xl shadow-md border border-zinc-100">🍎</div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-3xl font-bold tracking-tight" id="hist-ticker">AAPL</h2>
                                <span class="text-xs font-bold bg-zinc-100 text-zinc-500 px-2 py-1 rounded border border-zinc-200">NASDAQ</span>
                                <input type="text" id="tickerInput" value="AAPL" onchange="changeTicker(this.value)" 
                                    class="bg-transparent border-b border-zinc-300 text-zinc-600 uppercase w-20 text-sm focus:border-blue-600 focus:text-black outline-none transition-all placeholder:text-zinc-400 font-bold"
                                    placeholder="SEARCH">
                            </div>
                            <h1 class="text-xl text-zinc-500 font-medium">Apple Inc</h1>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-5xl font-bold tracking-tighter mb-1" id="hist-price">...</div>
                        <div class="flex items-center justify-end gap-2">
                             <span class="text-lg font-bold text-green-600 flex items-center" id="hist-change">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg> 
                                ...
                             </span>
                             <span class="text-zinc-500 text-sm font-medium">Today</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- LEFT COLUMN: Chart & Stats -->
                    <div class="lg:col-span-2 space-y-8">
                        
                        <!-- Chart Card -->
                        <div class="bg-white rounded-3xl border border-zinc-200 p-8 shadow-sm hover:shadow-md transition-shadow">
                             <!-- Range Tabs -->
                            <div class="flex gap-4 mb-6 border-b border-zinc-100 pb-1">
                                <button onclick="updateHistory('1D', this)" class="range-btn active text-blue-600 border-b-[3px] border-blue-600 pb-3 px-1 text-sm font-bold transition-all">1D</button>
                                <button onclick="updateHistory('1W', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">5D</button>
                                <button onclick="updateHistory('1M', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">1M</button>
                                <button onclick="updateHistory('3M', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">6M</button>
                                <button onclick="updateHistory('1Y', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">YTD</button>
                                <button onclick="updateHistory('1Y', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">1Y</button>
                                <button onclick="updateHistory('5Y', this)" class="range-btn text-zinc-500 hover:text-black hover:bg-zinc-50 pb-3 px-3 rounded-t-lg text-sm font-bold transition-all">5Y</button>
                            </div>

                            <div class="h-[400px] w-full relative">
                                <!-- Dashed "Previous Close" Line (Visual only, positioned absolute for effect) -->
                                <div class="absolute top-[30%] left-0 right-0 border-t border-dashed border-zinc-300 z-0 opacity-50"></div>
                                <canvas id="historyChart" class="relative z-10"></canvas>
                            </div>
                        </div>

                        <!-- Key Stats Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-y-6 gap-x-12 text-sm px-2">
                            <div class="border-b border-zinc-100 pb-2">
                                <div class="text-zinc-500 mb-1 font-medium">Open</div>
                                <div class="font-bold text-lg text-zinc-800">184.22</div>
                            </div>
                            <div class="border-b border-zinc-100 pb-2">
                                <div class="text-zinc-500 mb-1 font-medium">High</div>
                                <div class="font-bold text-lg text-zinc-800">186.50</div>
                            </div>
                            <div class="border-b border-zinc-100 pb-2">
                                <div class="text-zinc-500 mb-1 font-medium">Low</div>
                                <div class="font-bold text-lg text-zinc-800">183.89</div>
                            </div>
                            <div class="border-b border-zinc-100 pb-2">
                                <div class="text-zinc-500 mb-1 font-medium">Mkt cap</div>
                                <div class="font-bold text-lg text-zinc-800">3.02T</div>
                            </div>
                            <div class="border-b border-zinc-100 pb-2">
                                <div class="text-zinc-500 mb-1 font-medium">P/E ratio</div>
                                <div class="font-bold text-lg text-zinc-800">32.40</div>
                            </div>
                            <div class="border-b border-zinc-100 pb-2">
                                <div class="text-zinc-500 mb-1 font-medium">Div yield</div>
                                <div class="font-bold text-lg text-zinc-800">0.52%</div>
                            </div>
                            <div class="border-b border-zinc-100 pb-2">
                                <div class="text-zinc-500 mb-1 font-medium">52-wk high</div>
                                <div class="font-bold text-lg text-zinc-800">199.62</div>
                            </div>
                            <div class="border-b border-zinc-100 pb-2">
                                <div class="text-zinc-500 mb-1 font-medium">52-wk low</div>
                                <div class="font-bold text-lg text-zinc-800">124.17</div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN: Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Related List -->
                        <div class="bg-white rounded-3xl border border-zinc-200 p-6 shadow-sm">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-bold text-lg">Related Stocks</h3>
                                <button class="text-xs font-bold text-blue-600 hover:underline">View All</button>
                            </div>
                            
                            <div class="space-y-6">
                                <div class="flex justify-between items-center group cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center font-bold text-xs">MSFT</div>
                                        <div class="text-sm">
                                            <div class="font-bold group-hover:text-blue-600 transition-colors">Microsoft</div>
                                            <div class="text-zinc-500 text-xs">Software</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold">402.50</div>
                                        <span class="text-green-600 text-xs font-bold bg-green-50 px-2 py-1 rounded">+0.40%</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center group cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center font-bold text-xs">NVDA</div>
                                        <div class="text-sm">
                                            <div class="font-bold group-hover:text-blue-600 transition-colors">NVIDIA</div>
                                            <div class="text-zinc-500 text-xs">Semiconductors</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold">589.20</div>
                                        <span class="text-green-600 text-xs font-bold bg-green-50 px-2 py-1 rounded">+3.01%</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center group cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-xs">AMZN</div>
                                        <div class="text-sm">
                                            <div class="font-bold group-hover:text-blue-600 transition-colors">Amazon</div>
                                            <div class="text-zinc-500 text-xs">Retail</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold">155.30</div>
                                        <span class="text-green-600 text-xs font-bold bg-green-50 px-2 py-1 rounded">+1.62%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- News Card -->
                        <div class="bg-white rounded-3xl border border-zinc-200 p-6 shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="bg-blue-100 text-blue-600 text-[10px] font-black px-2 py-1 rounded uppercase tracking-wider">News</span>
                                <span class="text-xs font-bold text-zinc-400">MarketBeat &bull; 2 min ago</span>
                            </div>
                            <h4 class="font-bold text-base mb-3 leading-snug">
                                Buckhead Capital Management LLC Sells 5,148 Shares of Apple Inc. $AAPL
                            </h4>
                        </div>

                        <!-- Financials -->
                        <div class="bg-white rounded-3xl border border-zinc-200 p-6 shadow-sm">
                             <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-sm">Quarterly Financials</h3>
                                <i class="fa-solid fa-chevron-down text-zinc-400"></i>
                            </div>
                            <div class="flex justify-between items-end">
                                <div>
                                    <div class="text-3xl font-bold tracking-tight text-zinc-900">102.47B</div>
                                    <div class="text-xs text-zinc-500">Revenue (Q4 2025)</div>
                                </div>
                                <div class="text-green-600 text-xs font-bold bg-green-50 px-2 py-1 rounded mb-1">
                                    +7.94% Y/Y
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        <!-- Features -->
        <section class="py-24 bg-white border-t border-zinc-100">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="feature-card">
                        <div class="w-12 h-12 bg-zinc-100 rounded-xl flex items-center justify-center mb-6 text-xl">⚡</div>
                        <h3 class="text-xl font-bold mb-2">Real-Time Execution</h3>
                        <p class="text-zinc-500 leading-relaxed">Lightning fast order routing ensures you never miss a tick.</p>
                    </div>
                    <div class="feature-card">
                        <div class="w-12 h-12 bg-zinc-100 rounded-xl flex items-center justify-center mb-6 text-xl">🛡️</div>
                        <h3 class="text-xl font-bold mb-2">Bank-Grade Security</h3>
                        <p class="text-zinc-500 leading-relaxed">Your assets are protected by industry-leading encryption.</p>
                    </div>
                    <div class="feature-card">
                        <div class="w-12 h-12 bg-zinc-100 rounded-xl flex items-center justify-center mb-6 text-xl">📊</div>
                        <h3 class="text-xl font-bold mb-2">Advanced Analytics</h3>
                        <p class="text-zinc-500 leading-relaxed">Professional charting tools and market depth at your fingertips.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Rich Footer -->
        <footer class="bg-[#0f1014] text-white pt-20 pb-10">
            <div class="max-w-7xl mx-auto px-6">
                
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8 mb-16 border-b border-zinc-800/50 pb-12">
                    
                    <!-- Col 1 -->
                    <div class="space-y-4">
                        <h4 class="font-bold text-sm text-white">XTrade Products</h4>
                        <ul class="space-y-2 text-xs text-zinc-400">
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Futures</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Options</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Stocks & Fractional Shares</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">ETFs</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Crypto Trading</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">XTrade Advisors</a></li>
                        </ul>
                    </div>

                    <!-- Col 2 -->
                    <div class="space-y-4">
                        <h4 class="font-bold text-sm text-white">About XTrade</h4>
                        <ul class="space-y-2 text-xs text-zinc-400">
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Investor Relations</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Careers</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Pricing</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Blog</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">API Documentation</a></li>
                        </ul>
                    </div>

                    <!-- Col 3 -->
                    <div class="space-y-4">
                        <h4 class="font-bold text-sm text-white">Support</h4>
                        <ul class="space-y-2 text-xs text-zinc-400">
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Help Center</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Account Security</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Fee Schedule</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Contact Us</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">System Status</a></li>
                        </ul>
                    </div>

                    <!-- Col 4 -->
                    <div class="space-y-4">
                        <h4 class="font-bold text-sm text-white">Legal</h4>
                        <ul class="space-y-2 text-xs text-zinc-400">
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Terms of Service</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Risk Disclosure</a></li>
                            <li><a href="#" class="hover:text-blue-500 transition-colors">Data Disclaimer</a></li>
                        </ul>
                    </div>

                     <!-- Col 5: Contact -->
                     <div class="space-y-4 col-span-2 lg:col-span-2">
                        <h4 class="font-bold text-sm text-white">Contact & Social</h4>
                        <div class="text-xs text-zinc-400 space-y-1">
                            <div>support@xtrade.com</div>
                            <div>+1 (888) 555-0199</div>
                        </div>
                        <div class="flex gap-4 pt-2">
                            <a href="#" class="text-zinc-400 hover:text-white transition-colors"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-zinc-400 hover:text-white transition-colors"><i class="fab fa-instagram"></i></a> <!-- Fixed Icon Class -->
                            <a href="#" class="text-zinc-400 hover:text-white transition-colors"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-zinc-400 hover:text-white transition-colors"><i class="fab fa-youtube"></i></a>
                            <a href="#" class="text-zinc-400 hover:text-white transition-colors"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Platforms -->
                <div class="mb-12">
                    <h4 class="font-bold text-sm text-white mb-6">XTrade Platforms</h4>
                    <div class="flex flex-wrap gap-4">
                        <button class="flex items-center gap-3 px-6 py-3 border border-zinc-700 rounded-full hover:bg-zinc-800 transition-all hover:border-zinc-500 group">
                            <i class="fab fa-apple text-xl text-zinc-400 group-hover:text-white"></i>
                            <div class="text-left">
                                <div class="text-[10px] text-zinc-500 uppercase font-bold leading-none mb-1">Download for</div>
                                <div class="text-xs font-bold text-zinc-300 group-hover:text-white leading-none">macOS</div>
                            </div>
                        </button>
                         <button class="flex items-center gap-3 px-6 py-3 border border-zinc-700 rounded-full hover:bg-zinc-800 transition-all hover:border-zinc-500 group">
                            <i class="fab fa-windows text-xl text-zinc-400 group-hover:text-white"></i>
                            <div class="text-left">
                                <div class="text-[10px] text-zinc-500 uppercase font-bold leading-none mb-1">Download for</div>
                                <div class="text-xs font-bold text-zinc-300 group-hover:text-white leading-none">Windows</div>
                            </div>
                        </button>
                        <button class="flex items-center gap-3 px-6 py-3 border border-zinc-700 rounded-full hover:bg-zinc-800 transition-all hover:border-zinc-500 group">
                            <i class="fab fa-linux text-xl text-zinc-400 group-hover:text-white"></i>
                            <div class="text-left">
                                <div class="text-[10px] text-zinc-500 uppercase font-bold leading-none mb-1">Download for</div>
                                <div class="text-xs font-bold text-zinc-300 group-hover:text-white leading-none">Linux</div>
                            </div>
                        </button>
                         <div class="w-px h-10 bg-zinc-800 mx-2 hidden md:block"></div>
                         <button class="flex items-center gap-3 px-6 py-3 border border-zinc-700 rounded-full hover:bg-zinc-800 transition-all hover:border-zinc-500 group">
                            <i class="fab fa-app-store text-xl text-zinc-400 group-hover:text-white"></i>
                            <div class="text-left">
                                <div class="text-[10px] text-zinc-500 uppercase font-bold leading-none mb-1">Download on</div>
                                <div class="text-xs font-bold text-zinc-300 group-hover:text-white leading-none">App Store</div>
                            </div>
                        </button>
                         <button class="flex items-center gap-3 px-6 py-3 border border-zinc-700 rounded-full hover:bg-zinc-800 transition-all hover:border-zinc-500 group">
                            <i class="fab fa-google-play text-xl text-zinc-400 group-hover:text-white"></i>
                            <div class="text-left">
                                <div class="text-[10px] text-zinc-500 uppercase font-bold leading-none mb-1">Get it on</div>
                                <div class="text-xs font-bold text-zinc-300 group-hover:text-white leading-none">Google Play</div>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="text-center text-xs text-zinc-600 pt-8 border-t border-zinc-800/50">
                    &copy; <?= date('Y') ?> XTrade Financial LLC. No content on this site is investment advice. Investing involves risk.
                </div>
            </div>
        </footer>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Helper to generate mock data
            function genData(startPrice, count = 20, volatility = 5) {
                let data = [];
                let price = startPrice;
                for(let i=0; i<count; i++) {
                    price += (Math.random() - 0.5) * volatility;
                    data.push(price);
                }
                return data;
            }

            // Common Chart Options
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 0 },
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } },
                elements: { point: { radius: 0 }, line: { borderWidth: 2, tension: 0.4 } }
            };

            // 1. Stock Chart (AAPL)
            const ctxStocks = document.getElementById('chartStocks').getContext('2d');
            const dataStocks = genData(185, 20, 2);
            new Chart(ctxStocks, { type: 'line', data: { labels: dataStocks, datasets: [{ data: dataStocks, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true }] }, options: commonOptions });

            // 2. ETF Chart (SPY)
            const ctxEtfs = document.getElementById('chartEtfs').getContext('2d');
            const dataEtfs = genData(478, 20, 1.5);
            new Chart(ctxEtfs, { type: 'line', data: { labels: dataEtfs, datasets: [{ data: dataEtfs, borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true }] }, options: commonOptions });

            // 3. Crypto Chart (BTC) - LIVE Animated
            const ctxCrypto = document.getElementById('chartCrypto').getContext('2d');
            const initialBtc = genData(42150, 40, 50);
            
            const btcChart = new Chart(ctxCrypto, {
                type: 'line',
                data: {
                    labels: initialBtc.map(d=>''),
                    datasets: [{
                        data: initialBtc,
                        borderColor: '#f97316',
                        backgroundColor: (ctx) => {
                            const g = ctx.chart.ctx.createLinearGradient(0,0,0,200);
                            g.addColorStop(0, 'rgba(249, 115, 22, 0.2)');
                            g.addColorStop(1, 'rgba(249, 115, 22, 0)');
                            return g;
                        },
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 0,
                        tension: 0.4
                    }]
                },
                options: commonOptions
            });

            // Live Simulation Logic
            setInterval(() => {
                const last = btcChart.data.datasets[0].data[btcChart.data.datasets[0].data.length - 1];
                const change = (Math.random() - 0.5) * 60; // Volatility
                const newP = last + change;
                
                btcChart.data.datasets[0].data.push(newP);
                btcChart.data.datasets[0].data.shift();
                btcChart.update('none');

                // Update Text
                const el = document.getElementById('btc-price');
                if(el) {
                    el.innerText = '$' + newP.toLocaleString('en-US', {minimumFractionDigits: 2});
                    el.style.color = change > 0 ? '#10b981' : '#ef4444'; 
                    setTimeout(() => el.style.color = '', 400);
                }
            }, 800);

            // ==========================================
            // Google-Like Interaction Logic
            // ==========================================
            const ctxHist = document.getElementById('historyChart').getContext('2d');
            let currentTicker = 'AAPL';
            let currentRange = '1Y';
            let latestData = { price: 0, change: 0, percent: 0 }; // Store latest for reset

            // Custom Plugin: Vertical Crosshair Line
            const crosshairPlugin = {
                id: 'crosshair',
                afterDraw: (chart) => {
                    if (chart.tooltip?._active?.length) {
                        const ctx = chart.ctx;
                        const x = chart.tooltip._active[0].element.x;
                        const topY = chart.scales.y.top;
                        const bottomY = chart.scales.y.bottom;

                        ctx.save();
                        ctx.beginPath();
                        ctx.moveTo(x, topY);
                        ctx.lineTo(x, bottomY);
                        ctx.lineWidth = 1;
                        ctx.strokeStyle = '#d1d5db'; // zinc-300
                        ctx.setLineDash([5, 5]);
                        ctx.stroke();
                        ctx.restore();
                    }
                }
            };
            Chart.register(crosshairPlugin);

            // Gradients
            function getGradient(ctx, color) {
                const g = ctx.createLinearGradient(0, 0, 0, 400);
                g.addColorStop(0, color); 
                g.addColorStop(1, 'rgba(255, 255, 255, 0)');
                return g;
            }

            // Chart Instance
            let histChart = new Chart(ctxHist, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        borderColor: '#16a34a', 
                        backgroundColor: (ctx) => getGradient(ctx.chart.ctx, 'rgba(22, 163, 74, 0.2)'),
                        borderWidth: 2,
                        pointRadius: 0, // No points by default
                        pointHoverRadius: 6, // Big dot on hover
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#16a34a',
                        pointHoverBorderWidth: 3,
                        fill: true,
                        tension: 0.05
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            enabled: true, // We want the dot, but maybe minimize the box if we update header? 
                            // actually Google shows a small box + updates header. We'll do standard box for now.
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(255,255,255,0.9)',
                            titleColor: '#000',
                            bodyColor: '#000',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return '$' + parseFloat(context.parsed.y).toFixed(2);
                                }
                            }
                        }
                    },
                    scales: { 
                        x: { display: false }, 
                        y: { 
                            position: 'right', 
                            grid: { color: '#f3f4f6', borderDash: [4, 4] }, 
                            ticks: { color: '#9ca3af', font: { size: 11 } }
                        } 
                    },
                    onHover: (e, activeElements) => {
                        if (activeElements && activeElements.length > 0) {
                            const index = activeElements[0].index;
                            const dataPoint = histChart.data.datasets[0].data[index];
                            
                            // Calculate change from baseline (first point of the visible range)
                            // Or just show the raw price? Google shows raw price + change relative to previous day usually.
                            // For simplicity, we just show the Price at that moment.
                            // And maybe the change relative to the START of the chart? 
                            
                            const startPrice = histChart.data.datasets[0].data[0];
                            const change = dataPoint - startPrice;
                            const percent = (change / startPrice) * 100;
                            
                            updateHeaderDisplay(dataPoint, percent);
                        } else {
                            // Reset to latest
                            updateHeaderDisplay(latestData.price, latestData.percent);
                        }
                    }
                }
            });

            function updateHeaderDisplay(price, percent) {
                 const elPrice = document.getElementById('hist-price');
                 const elChange = document.getElementById('hist-change');

                 elPrice.innerText = '$' + parseFloat(price).toLocaleString('en-US', {minimumFractionDigits: 2});
                 
                 const isPos = percent >= 0;
                 elChange.innerHTML = (isPos ? 
                    `<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>` : 
                    `<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>`
                 ) + Math.abs(percent).toFixed(2) + '%';
                 
                 elChange.className = isPos ? 'text-lg font-bold text-green-600 flex items-center transition-colors' : 'text-lg font-bold text-red-600 flex items-center transition-colors';
            }

            function changeTicker(val) {
                currentTicker = val.toUpperCase();
                document.getElementById('hist-ticker').innerText = currentTicker;
                updateHistory(currentRange, null);
            }

            window.updateHistory = function(range, btn) {
                currentRange = range;
                if (btn) {
                    document.querySelectorAll('.range-btn').forEach(b => {
                        b.classList.remove('active', 'text-blue-600', 'border-b-[3px]', 'border-blue-600');
                        b.classList.add('text-zinc-500', 'hover:bg-zinc-50');
                    });
                    btn.classList.add('active', 'text-blue-600', 'border-b-[3px]', 'border-blue-600');
                    btn.classList.remove('text-zinc-500', 'hover:bg-zinc-50');
                }

                fetch(`api_history.php?ticker=${currentTicker}&range=${range}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) { return; }

                        histChart.data.labels = data.labels;
                        histChart.data.datasets[0].data = data.data;
                        
                        // Color Logic
                        const isPositive = data.change_percent >= 0;
                        const color = isPositive ? '#16a34a' : '#dc2626'; 
                        const bgBase = isPositive ? 'rgba(22, 163, 74, 0.2)' : 'rgba(220, 38, 38, 0.2)';

                        histChart.data.datasets[0].borderColor = color;
                        histChart.data.datasets[0].pointHoverBorderColor = color; 
                        histChart.data.datasets[0].backgroundColor = (ctx) => getGradient(ctx.chart.ctx, bgBase);
                        
                        histChart.update();

                        // Set Latest Data
                        const price = parseFloat(data.current_price);
                        const percent = parseFloat(data.change_percent);
                        latestData = { price, change: 0, percent }; // Simplified

                        updateHeaderDisplay(price, percent);
                    })
                    .catch(e => console.error(e));
            }
            
            updateHistory('1Y', document.querySelector('.range-btn.active'));
        </script>
        </script>
    </body>
    </html>
    <?php
    exit;
}

$uid = $_SESSION['user_id'];
$username = $_SESSION['full_name'];

// Fetch Balance
$stmt = $pdo->prepare("SELECT balance, buying_power FROM Accounts WHERE user_id = ?");
$stmt->execute([$uid]);
$account = $stmt->fetch();

if (!$account) {
    // Fallback init
    $pdo->prepare("INSERT INTO Accounts (user_id, balance, buying_power) VALUES (?, 10000.00, 10000.00)")->execute([$uid]);
    $account = ['balance' => 10000.00, 'buying_power' => 10000.00];
}

$balance = $account['balance'];
$buying_power = $account['buying_power'];

// Mock Market Data (Top Movers) for Dashboard
$movers = [
    ['ticker' => 'NVDA', 'price' => 480.00, 'change' => 2.5, 'name' => 'NVIDIA Corp'],
    ['ticker' => 'TSLA', 'price' => 245.50, 'change' => -1.2, 'name' => 'Tesla Inc'],
    ['ticker' => 'AAPL', 'price' => 190.25, 'change' => 0.8, 'name' => 'Apple Inc'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - XTrade Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <?php include 'includes/nav.php'; ?>

    <!-- Dashboard Content -->
    <div class="dashboard-header">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Good Morning, <?php echo htmlspecialchars(explode(' ', $username)[0]); ?></h1>
            <p class="text-muted">Here is your portfolio overview for today.</p>
        </div>
        <div>
            <span class="badge badge-green">Market Open</span>
        </div>
    </div>

    <div class="dashboard-grid">
        
        <!-- Portfolio Card (Main) -->
        <div class="card-premium col-span-2">
            <div class="flex-between" style="align-items: flex-start;">
                <div>
                    <div class="balance-label">Total Net Equity</div>
                    <div class="balance-amount">$<?php echo number_format($balance, 2); ?></div>
                    <div class="mt-2 text-small">
                        <span style="color: var(--accent); font-weight: 600;">+ $1,240.50 (2.4%)</span> 
                        <span class="text-muted">Today</span>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div class="balance-label">Buying Power</div>
                    <div style="font-size: 1.5rem; font-weight: 700;">$<?php echo number_format($buying_power, 2); ?></div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button class="btn-primary">Deposit Funds</button>
                <button class="btn-primary" style="background: #fff; color: #000; border: 1px solid #e4e4e7;">Withdraw</button>
            </div>
        </div>

        <!-- Market Overview Widget -->
        <div class="card-premium">
            <h3 class="mb-4">Top Movers</h3>
            <table class="table-minimal">
                <tbody>
                    <?php foreach ($movers as $stock): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700;"><?php echo $stock['ticker']; ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?php echo $stock['name']; ?></div>
                        </td>
                        <td style="text-align: right;">
                            <div>$<?php echo number_format($stock['price'], 2); ?></div>
                            <div style="font-size: 0.75rem; color: <?php echo $stock['change'] >= 0 ? 'var(--accent)' : 'var(--danger)'; ?>;">
                                <?php echo ($stock['change'] > 0 ? '+' : '') . $stock['change']; ?>%
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Portfolio Chart (Placeholder) -->
        <div class="card-premium col-span-3">
            <div class="flex-between mb-4">
                <h3>Performance</h3>
                <div style="display: flex; gap: 0.5rem;">
                    <button style="padding: 0.25rem 0.75rem; border-radius: 0.5rem; background: #f4f4f5; border: none; font-weight: 600; font-size: 0.75rem;">1D</button>
                    <button style="padding: 0.25rem 0.75rem; border-radius: 0.5rem; background: #fff; border: 1px solid #e4e4e7; font-weight: 600; font-size: 0.75rem;">1W</button>
                    <button style="padding: 0.25rem 0.75rem; border-radius: 0.5rem; background: #fff; border: 1px solid #e4e4e7; font-weight: 600; font-size: 0.75rem;">1M</button>
                </div>
            </div>
            <div style="height: 300px; width: 100%;">
                <canvas id="portfolioChart"></canvas>
            </div>
        </div>

    </div>

    <div style="margin-top: 50px; text-align: center; color: #ccc; font-size: 0.8rem; padding-bottom: 20px;">
        &copy; <?php echo date('Y'); ?> XTrade Financial LLC. All rights reserved.
    </div>

    </div><!-- End Main Wrapper -->
</body>
</html>