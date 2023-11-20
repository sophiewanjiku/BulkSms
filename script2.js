// Add an event listener to handle clicks on navigation items
const navItems = document.querySelectorAll('.nav-list li');
navItems.forEach(item => {
    item.addEventListener('click', () => {
        // Add your logic here to handle the click event on each navigation item
        // For example, you can implement page navigation or content display based on the clicked item
    });
});

// Implement a responsive design for the header section
const header = document.querySelector('.header');
const leftNav = document.querySelector('.left-nav');
window.addEventListener('resize', () => {
    if (window.innerWidth < 768) { // Adjust the breakpoint as needed
        header.style.flexDirection = 'column';
        leftNav.style.display = 'none';
    } else {
        header.style.flexDirection = 'row';
        leftNav.style.display = 'block';
    }
});
// Optional: You can add JavaScript to close the dropdown when clicking outside of it
document.addEventListener('click', function (event) {
    var dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(function (dropdown) {
        if (!dropdown.contains(event.target)) {
            dropdown.querySelector('.dropdown-content').style.display = 'none';
        }
    });
});




// Add your logic for creating and displaying interactive pie charts using a library like Chart.js
// Example:
// const ctx = document.getElementById('savingsPieChart').getContext('2d');
// const savingsPieChart = new Chart(ctx, {
//     type: 'pie',
//     data: {
//         // Add your data here
//     },
//     options: {
//         // Add your options here
//     }
// });

// Similar logic for the loan repayment pie chart

// You can add more interactive elements and functionalities as needed
