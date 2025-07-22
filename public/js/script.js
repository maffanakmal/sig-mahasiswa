
const hamburger = document.querySelector('.toggle-btn');
const toggler = document.querySelector('.toggle-btn box-icon'); // Ambil <box-icon> langsung

hamburger.addEventListener('click', function () {
    document.querySelector('#dashboard-sidebar').classList.toggle('active');
    
    const currentIcon = toggler.getAttribute('name');
    toggler.setAttribute('name', currentIcon === 'menu' ? 'x' : 'menu');
});
