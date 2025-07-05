function previewImage(event) {
    var input = event.target;
    var preview = document.getElementById('preview');
    
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      
      reader.onload = function (e) {
        preview.src = e.target.result;
      }
      
      reader.readAsDataURL(input.files[0]);
    }
}

function getCar(x) {
    var select = document.getElementById("cars"+x);
    var selectedValue = select.value;
    var newValue = selectedValue.substring(selectedValue.indexOf("_")+1);
    document.getElementById("displayCar"+x).src = "uploads/cars/"+newValue;
}

function getTrack() {
    var select = document.getElementById("tracks");
    var selectedValue = select.value;
    var newValue = selectedValue.substring(selectedValue.indexOf("_")+1);
    document.getElementById("displayTrack").src = "uploads/tracks/"+newValue;
}

function updateCarImage(carNumber) {
    var select = document.getElementById("cars" + carNumber);
    var selectedOption = select.options[select.selectedIndex];
    var imageName = selectedOption.getAttribute('data-img');
    var imageContainer = document.getElementById('carImage' + carNumber);
    
    if (imageName && imageContainer) {
        imageContainer.innerHTML = '<img src="uploads/cars/' + imageName + '" alt="" class="preview">';
    }
}

function updateTrackImage() {
    var select = document.getElementById("tracks");
    var selectedOption = select.options[select.selectedIndex];
    var imageName = selectedOption.getAttribute('data-img');
    var imageContainer = document.getElementById('trackImage');
    
    if (imageName && imageContainer) {
        imageContainer.innerHTML = '<img src="uploads/tracks/' + imageName + '" alt="" class="preview">';
    }
}

const menuToggle = document.getElementById('menuToggle');
const mainNav = document.getElementById('mainNav');
let menuActive = false;

// Mobile menu toggle functionality
if (menuToggle && mainNav) {
    menuToggle.addEventListener('click', function() {
        if (menuActive) {
            // Hide menu
            mainNav.style.marginLeft = '-100%';
            menuActive = false;
        } else {
            // Show menu
            mainNav.style.marginLeft = '0';
            menuActive = true;
        }
    });

    // Close menu when clicking outside of it
    document.addEventListener('click', function(event) {
        if (menuActive && !mainNav.contains(event.target) && !menuToggle.contains(event.target)) {
            mainNav.style.marginLeft = '-100%';
            menuActive = false;
        }
    });

    // Close menu when window is resized to desktop size
    window.addEventListener('resize', function() {
        if (window.innerWidth > 767) {
            mainNav.style.marginLeft = '0';
            menuActive = false;
        } else if (!menuActive) {
            mainNav.style.marginLeft = '-100%';
        }
    });
}