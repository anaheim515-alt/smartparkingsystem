/* ===== OPEN/CLOSE SLOT POPUP ===== */
function openPopup() {
    document.getElementById('slotPopup').style.display = 'block';
    document.getElementById('popupOverlay').style.display = 'block';
}

function closePopup() {
    document.getElementById('slotPopup').style.display = 'none';
    document.getElementById('popupOverlay').style.display = 'none';
}

/* ===== TOAST MESSAGE ===== */
function showMessage(msg, type = 'info') {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'popup-msg';
    messageDiv.style.backgroundColor = type === 'error' ? '#e74c3c' : '#35408e';
    messageDiv.style.color = type === 'error' ? '#fff' : '#ffd41c';
    messageDiv.innerText = msg;
    document.body.appendChild(messageDiv);

    setTimeout(() => {
        messageDiv.style.transition = 'opacity 0.5s';
        messageDiv.style.opacity = '0';
        setTimeout(() => document.body.removeChild(messageDiv), 500);
    }, 3000);
}

/* ===== UPDATE SLOT TABLE ===== */
function updateSlots(highlightSlot = null) {
    fetch('slots_table.php')
        .then(res => res.text())
        .then(data => {
            const tbody = document.querySelector('table tbody');
            tbody.innerHTML = data;

            // Highlight the slot that just became available
            if(highlightSlot){
                const rows = tbody.querySelectorAll('tr');
                rows.forEach(row => {
                    if(row.cells[0].innerText == highlightSlot){
                        row.cells[1].style.backgroundColor = '#28a745'; // green
                        row.cells[1].style.color = 'white';
                        row.cells[1].style.fontWeight = 'bold';
                        setTimeout(() => { row.cells[1].style = ''; }, 3000); // remove highlight after 3s
                    }
                });
            }
        });
}

/* ===== PARK CAR ===== */
document.getElementById('parkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let plate = document.getElementById('plate').value.trim().toUpperCase();

    // Validate plate format: AAA 111 or ABC 1234
    const plateRegex = /^[A-Z]{3}\s\d{3,4}$/;
    if(!plateRegex.test(plate)){
        showMessage('Invalid plate format. Use AAA 111 or ABC 1234', 'error');
        return;
    }

    // Fetch available slots
    fetch('get_slots.php')
        .then(res => res.json())
        .then(slots => {
            const container = document.getElementById('slotContainer');
            container.innerHTML = '';

            if(slots.length === 0){
                showMessage('No available slots!', 'error');
                return;
            }

            slots.forEach(slot => {
                const btn = document.createElement('button');
                btn.innerText = slot.slot_id;
                btn.className = 'slot-button slot-available';
                btn.onclick = () => parkCar(plate, slot.slot_id);
                container.appendChild(btn);
            });

            openPopup();
        });
});

function parkCar(plate, slot_id) {
    fetch('park.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `plate=${plate}&slot_id=${slot_id}`
    })
    .then(res => res.text())
    .then(msg => {
        showMessage(msg);
        closePopup();
        updateSlots(); // no highlight needed for park
        document.getElementById('parkForm').reset();
    })
    .catch(() => showMessage('Error parking car', 'error'));
}

/* ===== LEAVE CAR ===== */
document.getElementById('leaveForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);

    fetch('leave.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(msg => {
            showMessage(msg);
            
            // Extract slot number from message
            const slotMatch = msg.match(/slot (\d+)/);
            const freedSlot = slotMatch ? slotMatch[1] : null;

            updateSlots(freedSlot); // highlight the freed slot
            this.reset();
        })
        .catch(() => showMessage('Error processing leave', 'error'));
});

/* ===== AUTO FORMAT INPUT ===== */
const plateInput = document.getElementById('plate');
plateInput.addEventListener('input', function() {
    let value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,'');
    if(value.length > 3){
        value = value.slice(0,3) + ' ' + value.slice(3,7); // max 4 digits
    }
    this.value = value;
});
