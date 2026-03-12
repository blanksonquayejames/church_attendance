document.addEventListener('DOMContentLoaded', () => {
    // Current date/time setup for arrival
    const arrivalInput = document.getElementById('arrivalTime');
    
    function getLocalISOTime() {
        const current = new Date();
        const offset = current.getTimezoneOffset() * 60000;
        return (new Date(current - offset)).toISOString().slice(0, 16);
    }
    
    const localISOTime = getLocalISOTime();
    arrivalInput.value = localISOTime;
    
    // Auto-select and lock the field so the user cannot change it
    arrivalInput.readOnly = true;
    arrivalInput.style.pointerEvents = 'none';
    arrivalInput.style.backgroundColor = '#e2e8f0';
    arrivalInput.style.color = '#64748b';
    
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000;
    const localISODate = (new Date(now - offset)).toISOString().slice(0, 10);

    // Date of Birth setup: Prevent selecting future dates by setting the max attribute to today
    const dobInput = document.getElementById('dob');
    if (dobInput) {
        dobInput.max = localISODate;
    }

    // Auto-fill member check
    const firstNameInput = document.getElementById('firstName');
    const surnameInput = document.getElementById('surname');
    const oldMemberRadio = document.querySelector('input[name="membership"][value="old"]');
    const newMemberRadio = document.querySelector('input[name="membership"][value="new"]');
    const deptInput = document.getElementById('department');
    const locInput = document.getElementById('location');
    const pobInput = document.getElementById('pob');

    // Keep track of the last user we auto-filled for
    let lastAutofilledUser = "";
    let typingTimer;
    const doneTypingInterval = 500; // 500ms debounce

    async function fetchMemberData() {
        const fName = firstNameInput.value.trim();
        const lName = surnameInput.value.trim();
        const currentFullName = fName.toLowerCase() + " " + lName.toLowerCase();
        
        if (fName.length > 0 && lName.length > 0 && currentFullName !== lastAutofilledUser) {
            try {
                const response = await fetch(`check_member.php?firstName=${encodeURIComponent(fName)}&surname=${encodeURIComponent(lName)}`);
                const data = await response.json();
                
                if (data.is_old_member) {
                    oldMemberRadio.checked = true;
                    deptInput.value = data.department;
                    locInput.value = data.location;
                    if (dobInput) dobInput.value = data.dob;
                    pobInput.value = data.pob;
                    lastAutofilledUser = currentFullName;
                    
                    const radioGroup = document.querySelector('.radio-group');
                    const originalBg = radioGroup.style.backgroundColor;
                    radioGroup.style.transition = 'background-color 0.5s ease';
                    radioGroup.style.backgroundColor = 'rgba(99, 102, 241, 0.15)';
                    setTimeout(() => {
                        radioGroup.style.backgroundColor = originalBg;
                    }, 1500);
                }
            } catch(e) {
                console.error("Error checking for existing member data", e);
            }
        }
    }

    function handleNameInput() {
        const fName = firstNameInput.value.trim();
        const lName = surnameInput.value.trim();
        const currentFullName = fName.toLowerCase() + " " + lName.toLowerCase();
        
        // Instantly reset if the name no longer matches the auto-filled name
        if (lastAutofilledUser !== "" && currentFullName !== lastAutofilledUser) {
            oldMemberRadio.checked = false;
            newMemberRadio.checked = false;
            deptInput.value = '';
            locInput.value = '';
            if (dobInput) dobInput.value = '';
            pobInput.value = '';
            lastAutofilledUser = "";
        }

        // Clear timer so we don't fetch while user is actively typing
        clearTimeout(typingTimer);
        
        // Only fetch if they have typed something in both fields and we haven't already filled it
        if (fName.length > 0 && lName.length > 0 && currentFullName !== lastAutofilledUser) {
            typingTimer = setTimeout(fetchMemberData, doneTypingInterval);
        }
    }

    // Switch from 'blur' to 'input' so it fires on every keystroke
    firstNameInput.addEventListener('input', handleNameInput);
    surnameInput.addEventListener('input', handleNameInput);

    // Camera Functionality
    const cameraStartView = document.getElementById('cameraStartView');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const videoContainer = document.getElementById('videoContainer');
    const cameraFeed = document.getElementById('cameraFeed');
    const captureBtn = document.getElementById('captureBtn');
    const cameraCanvas = document.getElementById('cameraCanvas');
    const previewContainer = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const retakeBtn = document.getElementById('retakeBtn');
    const faceDataInput = document.getElementById('faceVerificationData');

    let stream = null;

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
            cameraFeed.srcObject = stream;
            cameraStartView.style.display = 'none';
            videoContainer.classList.remove('hidden');
        } catch (err) {
            alert('Error accessing camera: ' + err.message);
            console.error('Camera error:', err);
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    startCameraBtn.addEventListener('click', startCamera);

    captureBtn.addEventListener('click', () => {
        // Set canvas to match video dimensions
        cameraCanvas.width = cameraFeed.videoWidth;
        cameraCanvas.height = cameraFeed.videoHeight;

        // Draw the current video frame to canvas
        const ctx = cameraCanvas.getContext('2d');
        ctx.drawImage(cameraFeed, 0, 0, cameraCanvas.width, cameraCanvas.height);

        // Convert to data URL (base64 image)
        const imageData = cameraCanvas.toDataURL('image/png');

        // Set preview image and input value
        previewImg.src = imageData;
        faceDataInput.value = imageData;

        // Stop camera and show preview
        stopCamera();
        videoContainer.classList.add('hidden');
        previewContainer.classList.remove('hidden');
    });

    retakeBtn.addEventListener('click', () => {
        previewContainer.classList.add('hidden');
        faceDataInput.value = '';
        startCamera();
    });

    // Form submission animation & Backend API Call
    const form = document.getElementById('attendanceForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Ensure a picture is captured
        if (!faceDataInput.value) {
            alert("Please capture a live photo before submitting.");
            return;
        }

        // Ensure arrival date is not in the past
        const selectedDate = new Date(arrivalInput.value);
        const todayStart = new Date();
        todayStart.setHours(0, 0, 0, 0);
        
        if (selectedDate < todayStart) {
            alert("Past dates for arrival are not allowed. please select today or a future date.");
            return;
        }

        const btn = form.querySelector('.submit-btn');
        const originalHtml = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span> Processing...</span>';
        btn.disabled = true;
        btn.style.opacity = '0.8';
        btn.style.cursor = 'not-allowed';

        // Auto-select exact current time for submission
        arrivalInput.value = getLocalISOTime();

        try {
            const formData = new FormData(form);

            // Send Data to PHP Backend
            const response = await fetch('submit.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Success State!
                btn.innerHTML = '<i class="fas fa-check"></i><span> ' + result.message + '</span>';
                btn.style.background = 'linear-gradient(135deg, #10b981, #34d399)';

                setTimeout(() => {
                    form.reset();
                    arrivalInput.value = localISOTime; // Reset base date
                    previewContainer.classList.add('hidden'); // Reset camera preview box
                    previewImg.src = '';
                    faceDataInput.value = '';
                    cameraStartView.style.display = 'flex'; // Bring back camera prompt text

                    // Restore original button
                    btn.innerHTML = originalHtml;
                    btn.style.background = '';
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                    btn.disabled = false;
                }, 3000);
            } else {
                // Show Error received from PHP backend
                alert("Error: " + result.message);
                restoreButton(btn, originalHtml);
            }

        } catch (error) {
            console.error("Fetch error: ", error);
            alert("Network connection error, please try again.");
            restoreButton(btn, originalHtml);
        }
    });

    function restoreButton(btn, html) {
        btn.innerHTML = html;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
        btn.disabled = false;
    }
});
