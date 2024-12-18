document.addEventListener('DOMContentLoaded', function() {
    // Municipality and Barangay selection logic
    const municipalitySelect = document.getElementById('municipality');
    const barangaySelect = document.getElementById('barangay');

    const barangayOptions = {
        'San Manuel': ['Barangay 1', 'Barangay 2'],
        // Add more municipalities and their barangays as needed
    };

    municipalitySelect.addEventListener('change', function() {
        const selectedMunicipality = this.value;
        barangaySelect.innerHTML = '<option value="">Select a Barangay</option>';

        if (selectedMunicipality && barangayOptions[selectedMunicipality]) {
            barangayOptions[selectedMunicipality].forEach(function(barangay) {
                const option = document.createElement('option');
                option.value = barangay;
                option.textContent = barangay;
                barangaySelect.appendChild(option);
            });
        }
    });

    const daySelect = document.getElementById('dob-day');
    const monthSelect = document.getElementById('dob-month');
    const yearSelect = document.getElementById('dob-year');
    const ageInput = document.getElementById('age');

   // Populate months (01-12)
const monthNames = ["January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

monthNames.forEach((month, index) => {
    const option = document.createElement('option');
    const monthValue = (index + 1).toString().padStart(2, '0');  // Ensure two-digit format
    option.value = monthValue;  // Save as a two-digit month (e.g., "07" for July)
    option.textContent = month;
    monthSelect.appendChild(option);
});


    // Populate years (from 1900 to 1970)
    for (let i = 1964; i >= 1900; i--) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i;
        yearSelect.appendChild(option);
    }

    // Update days based on the selected month and year
    function updateDays() {
        const selectedYear = parseInt(yearSelect.value);
        const selectedMonth = parseInt(monthSelect.value);

        // Clear existing day options
        daySelect.innerHTML = '<option value="" disabled selected>Day</option>';

        if (!isNaN(selectedYear) && !isNaN(selectedMonth)) {
            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();

            for (let i = 1; i <= daysInMonth; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                daySelect.appendChild(option);
            }
        }
    }

    // Function to calculate age based on selected day, month, and year
    function calculateAge() {
        const selectedDay = parseInt(daySelect.value);
        const selectedMonth = parseInt(monthSelect.value);
        const selectedYear = parseInt(yearSelect.value);

        if (isNaN(selectedDay) || isNaN(selectedMonth) || isNaN(selectedYear)) {
            ageInput.value = '';
            return;
        }

        const today = new Date();
        const birthDate = new Date(selectedYear, selectedMonth - 1, selectedDay);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        ageInput.value = age;
    }

    // Event listeners
    monthSelect.addEventListener('change', () => {
        updateDays();
        calculateAge();
    });

    yearSelect.addEventListener('change', () => {
        updateDays();
        calculateAge();
    });

    daySelect.addEventListener('change', calculateAge);


    // Adding a family member
    document.getElementById('add-family-member').addEventListener('click', function() {
        const familySection = document.querySelector('.family-composition');
        const newFamilyRow = document.createElement('div');
        newFamilyRow.classList.add('input-group');

        newFamilyRow.innerHTML = `
                <div class="input-item">
                    <input type="text" name="family_name[]" placeholder="Name">
                </div>
                <div class="input-item">
                    <input type="text" name="relationship[]" placeholder="Relationship">
                </div>
                <div class="input-item">
                    <input type="number" name="family_age[]" placeholder="Age">
                </div>
                <div class="input-item">
                    <input type="text" name="family_civil_status[]" placeholder="Civil Status">
                </div>
                <div class="input-item">
                    <input type="text" name="family_occupation[]" placeholder="Occupation">
                </div>
            `;
        familySection.appendChild(newFamilyRow);
    });

    document.getElementById('add-beneficiary').addEventListener('click', function() {
        const beneficiarySection = document.querySelector('.designated-beneficiary');
        const newBeneficiaryRow = document.createElement('div');
        newBeneficiaryRow.classList.add('input-group');

        newBeneficiaryRow.innerHTML = `
                <div class="input-item">
                    <input type="text" name="beneficiary_name[]" placeholder="Name" required>
                </div>
                <div class="input-item">
                    <input type="text" name="beneficiary_relationship[]" placeholder="Relationship" required>
                </div>
                <div class="input-item">
                    <input type="number" name="beneficiary_age[]" placeholder="Age" required>
                </div>
                <div class="input-item">
                    <input type="text" name="beneficiary_civil_status[]" placeholder="Civil Status" required>
                </div>
                <div class="input-item">
                    <input type="text" name="beneficiary_occupation[]" placeholder="Occupation" required>
                </div>
            `;
        beneficiarySection.appendChild(newBeneficiaryRow);
    });
    
    // Initialize elements for the photo section
    const video = document.getElementById('photo-preview');
    const imgPlaceholder = document.getElementById('photo-placeholder');
    const captureButton = document.getElementById('capture-photo-btn');
    const uploadFileInput = document.getElementById('upload-photo-file');
    const openCameraButton = document.getElementById('open-photo-camera-btn');
    const customUploadButton = document.getElementById('custom-upload-btn');
    const closeCameraButton = document.getElementById('close-camera-btn');

    // Initialize elements for the certificate/ID section
    const certificateVideo = document.getElementById('id-preview');
    const certificatePlaceholder = document.getElementById('id-placeholder');
    const captureCertificateButton = document.getElementById('capture-certificate-btn');
    const uploadCertificateFileInput = document.getElementById('upload-certificate-file');
    const openCertificateCameraButton = document.getElementById('open-certificate-camera-btn');
    const customUploadCertificateButton = document.getElementById('custom-upload-certificate-btn'); // Added this line
    const closeCertificateCameraButton = document.getElementById('close-id-camera-btn');

    // Hidden inputs to store the captured images as base64 strings
    const photoDataInput = document.getElementById('photo-data');
    const certificateDataInput = document.getElementById('certificate-data');

    // Handle opening the camera for the photo section
    openCameraButton.addEventListener('click', function() {
        imgPlaceholder.style.display = 'none';
        video.style.display = 'block';
        closeCameraButton.style.display = 'block';

        // Start the video stream
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
                captureButton.style.display = 'inline-block';
            })
            .catch(error => {
                console.error("Error accessing the camera: ", error);
            });
    });

    // Capture the photo from the video stream
    captureButton.addEventListener('click', function() {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
    
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
    
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
        const photoDataURL = canvas.toDataURL('image/png');
        imgPlaceholder.src = photoDataURL;
        imgPlaceholder.style.display = 'block';
        video.style.display = 'none';
        closeCameraButton.style.display = 'none';
    
        photoDataInput.value = photoDataURL; // Set the hidden field value
    
        video.srcObject.getTracks().forEach(track => track.stop());
        captureButton.style.display = 'none';
    });

    // Handle custom upload button click for the photo section
    customUploadButton.addEventListener('click', function() {
        uploadFileInput.click(); // Trigger the hidden file input
    });

    // Handle uploading a file for the photo section
    uploadFileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const photoDataURL = e.target.result;
                imgPlaceholder.src = photoDataURL;
                imgPlaceholder.style.display = 'block';
                video.style.display = 'none';
                closeCameraButton.style.display = 'none';

                // Store the base64 image data in a hidden input for form submission
                photoDataInput.value = photoDataURL;

                captureButton.style.display = 'none';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Handle closing the camera feed for the photo section
    closeCameraButton.addEventListener('click', function() {
        if (video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
        }
        video.style.display = 'none';
        imgPlaceholder.style.display = 'block';
        closeCameraButton.style.display = 'none';

        captureButton.style.display = 'none';
    });

    // Handle opening the camera for the certificate/ID section
    openCertificateCameraButton.addEventListener('click', function() {
        certificatePlaceholder.style.display = 'none';
        certificateVideo.style.display = 'block';
        closeCertificateCameraButton.style.display = 'block';

        // Start the video stream
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                certificateVideo.srcObject = stream;
                captureCertificateButton.style.display = 'inline-block';
            })
            .catch(error => {
                console.error("Error accessing the camera: ", error);
            });
    });

    // Capture the certificate/ID from the video stream
    captureCertificateButton.addEventListener('click', function() {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
    
        canvas.width = certificateVideo.videoWidth;
        canvas.height = certificateVideo.videoHeight;
    
        context.drawImage(certificateVideo, 0, 0, canvas.width, canvas.height);
    
        const certificateDataURL = canvas.toDataURL('image/png');
        certificatePlaceholder.src = certificateDataURL;
        certificatePlaceholder.style.display = 'block';
        certificateVideo.style.display = 'none';
        closeCertificateCameraButton.style.display = 'none';
    
        certificateDataInput.value = certificateDataURL; // Set the hidden field value
    
        certificateVideo.srcObject.getTracks().forEach(track => track.stop());
        captureCertificateButton.style.display = 'none';
    });


    // Handle custom upload button click for the certificate/ID section
    customUploadCertificateButton.addEventListener('click', function() { // Added this event listener
        uploadCertificateFileInput.click(); // Trigger the hidden file input
    });

    // Handle uploading a file for the certificate/ID section
    uploadCertificateFileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const certificateDataURL = e.target.result;
                certificatePlaceholder.src = certificateDataURL;
                certificatePlaceholder.style.display = 'block';
                certificateVideo.style.display = 'none';
                closeCertificateCameraButton.style.display = 'none';

                // Store the base64 image data in a hidden input for form submission
                certificateDataInput.value = certificateDataURL;

                captureCertificateButton.style.display = 'none';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Handle closing the camera feed for the certificate/ID section
    closeCertificateCameraButton.addEventListener("click", function() {
        if (certificateVideo.srcObject) {
            certificateVideo.srcObject.getTracks().forEach(track => track.stop());
        }
        certificateVideo.style.display = "none";
        certificatePlaceholder.style.display = "block";
        closeCertificateCameraButton.style.display = "none";

        captureCertificateButton.style.display = "none";
    });



});