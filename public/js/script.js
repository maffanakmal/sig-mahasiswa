
const hamburger = document.querySelector('.toggle-btn');
const toggler = document.querySelector('.toggle-btn i'); // Ambil ikon di dalam tombol

hamburger.addEventListener('click', function () {
    document.querySelector('#dashboard-sidebar').classList.toggle('active');
    toggler.classList.toggle('bx-menu');
    toggler.classList.toggle('bx-x');
})