<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusEase</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes fade-in-left {
            from {
                opacity: 0;
                transform: translateX(-100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.65s ease-in-out forwards;
        }

        .animate-fade-in-left {
            animation: fade-in-left 1.05s ease-in-out forwards;
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-100 font-sans">

    <!-- Full Height Wrapper -->
    <div class="min-h-screen flex flex-col relative">

        <!-- Background Image -->
        <img class="absolute w-full h-screen object-cover" src="./assets/img/black earth.jpg" alt="Background Image">

        <!-- Header with Logo -->
        <div class="absolute flex items-center space-x-4 top-5 left-5 opacity-0 animate-fade-in">
            <img class="w-16 h-16 rounded-full shadow-lg border-2 border-gray-700" src="./assets/img/20240912_163056.jpg"
                alt="Profile Picture">
            <div class="font-mono text-yellow-400 text-3xl font-extrabold">
                BusEase
            </div>
        </div>

        <!-- Description Section -->
        <div class="absolute top-64 md:top-72 left-8 md:left-16 space-y-4 opacity-0 animate-fade-in-left">
            <h1 class="text-4xl md:text-6xl font-bold leading-tight tracking-wide text-gray-100">
                Make Your College <br> Ride Easier!
            </h1>
            <p class="text-lg md:text-xl text-gray-300">
                A new experience for students traveling by college bus.<br>
                Enjoy the journey with comfort and ease.
            </p>
        </div>

        <!-- Call-to-Action Button -->
        <div class="absolute flex top-[38rem] md:top-[32rem] left-8 md:left-16">
            <a href="access.php">
                <button
                    class="relative bg-yellow-500 text-black font-bold uppercase rounded-full px-12 py-4 overflow-hidden transition duration-300 transform hover:opacity-90 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-yellow-300">
                    <div class="absolute inset-0 flex items-center justify-center bg-white text-black transition-transform duration-300 transform hover:translate-y-full">
                        Get Started
                    </div>
                    <div class="flex space-x-1 opacity-0 hover:opacity-100">
                        <span>G</span>
                        <span>E</span>
                        <span>T</span>
                        <span class="ml-1">S</span>
                        <span>T</span>
                        <span>A</span>
                        <span>R</span>
                        <span>T</span>
                        <span>E</span>
                        <span>D</span>
                    </div>
                </button>
            </a>
        </div>

        <!-- Footer -->
        <footer class="mt-auto bg-gray-800 py-4 text-center text-sm text-gray-400">
            © 2025 BusEase. All rights reserved.
        </footer>
    </div>

</body>

</html>
