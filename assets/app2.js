function switchTab(tab) {
    const deploymentList = document.getElementById('available-list-deployment');
    const companyList = document.getElementById('available-list-company');
    if (!deploymentList || !companyList) return;

    deploymentList.style.display = tab === 'deployment' ? 'block' : 'none';
    companyList.style.display = tab === 'company' ? 'block' : 'none';

    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.tab-btn[onclick*="${tab}"]`).classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => {
    let availableAppsDeployment = [];
    let availableAppsCompany = [];
    let selectedApps = [];
    let pendingDelete = null;

    function showDeleteModal(appName) {
        const modal = document.getElementById('confirm-modal');
        const message = document.getElementById('confirm-message');
        message.textContent = `Are you sure you want to delete "${appName}"?`;
        modal.style.display = 'flex';
    }

    document.getElementById('confirm-yes').onclick = () => {
        if (!pendingDelete) return;

        fetch('./assets/delete-script.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: pendingDelete.Name, directoryId })
        }).then(() => {
            fetchAvailableApps();
            alert(`Script "${pendingDelete.Name}" deleted.`);
            pendingDelete = null;
            document.getElementById('confirm-modal').style.display = 'none';
        });
    };

    document.getElementById('confirm-no').onclick = () => {
        pendingDelete = null;
        document.getElementById('confirm-modal').style.display = 'none';
    };

    function renderAvailableLists() {
        const deploymentList = document.getElementById('available-list-deployment');
        const companyList = document.getElementById('available-list-company');
        if (!deploymentList || !companyList) return;

        deploymentList.innerHTML = '';
        companyList.innerHTML = '';

        availableAppsDeployment.forEach(app => {
            const li = document.createElement('li');
            li.textContent = app.Name;
          	li.className = 'app-item';
            const addBtn = document.createElement('button');
            addBtn.textContent = '➕ Add';
            addBtn.className = 'btn-add';
            addBtn.onclick = () => {
                selectedApps.push(app);
                renderSelectedList();
            };
            li.appendChild(addBtn);
            deploymentList.appendChild(li);
        });

        availableAppsCompany.forEach(app => {
            const li = document.createElement('li');
            li.textContent = app.Name;
          	li.className = 'app-item';

            const addBtn = document.createElement('button');
            addBtn.textContent = '➕ Add';
            addBtn.className = 'btn-add';
            addBtn.onclick = () => {
                selectedApps.push(app);
                renderSelectedList();
            };

            const editBtn = document.createElement('button');
            editBtn.textContent = '✏️ Edit';
            editBtn.className = 'btn-edit';
            editBtn.onclick = () => {
                window.location.href = `./edit_script.php?name=${encodeURIComponent(app.Name)}&dir=${directoryId}`;
            };

            const delBtn = document.createElement('button');
            delBtn.textContent = '🗑️ Delete';
            delBtn.className = 'btn-del';
            delBtn.onclick = () => {
                pendingDelete = app;
                showDeleteModal(app.Name);
            };

            li.appendChild(addBtn);
            li.appendChild(editBtn);
            li.appendChild(delBtn);
            companyList.appendChild(li);
        });
    }

    function renderSelectedList() {
        const selectedList = document.getElementById('selected-list');
        if (!selectedList) return;

        selectedList.innerHTML = '';

        selectedApps.forEach((app, index) => {
            const li = document.createElement('li');
            li.textContent = app.Name;
          	li.className = 'app-item';

            const upBtn = document.createElement('button');
            upBtn.textContent = '🔼';
            upBtn.className = 'btn-up';
            upBtn.onclick = () => {
                if (index > 0) {
                    [selectedApps[index - 1], selectedApps[index]] = [selectedApps[index], selectedApps[index - 1]];
                    renderSelectedList();
                }
            };

            const downBtn = document.createElement('button');
            downBtn.textContent = '🔽';
            downBtn.className = 'btn-down';
            downBtn.onclick = () => {
                if (index < selectedApps.length - 1) {
                    [selectedApps[index + 1], selectedApps[index]] = [selectedApps[index], selectedApps[index + 1]];
                    renderSelectedList();
                }
            };

            const delBtn = document.createElement('button');
            delBtn.textContent = '🗑️';
            delBtn.className = 'btn-del';
            delBtn.onclick = () => {
                selectedApps.splice(index, 1);
                renderSelectedList();
            };

            li.appendChild(upBtn);
            li.appendChild(downBtn);
            li.appendChild(delBtn);
            selectedList.appendChild(li);
        });
    }

    function fetchAvailableApps() {
        fetch('https://deploysmart.dev.mspot.se/deployment/applications.available.json')
            .then(response => {
                if (!response.ok) throw new Error("Deployment JSON not found");
                return response.json();
            })
            .then(data => {
                availableAppsDeployment = data;
                renderAvailableLists();
            })
            .catch(err => {
                console.error("Failed to load deployment applications:", err);
            });

        const companyPath = `./config/${directoryId}/applications.available.json`;
        fetch(companyPath)
            .then(response => {
                if (!response.ok) throw new Error("Company JSON not found");
                return response.json();
            })
            .then(data => {
                availableAppsCompany = data;
                renderAvailableLists();
            })
            .catch(err => {
                console.error("Failed to load company applications:", err);
            });
    }

    function fetchSelectedApps() {
        const path = `./config/${directoryId}/applications.json`;
        fetch(path)
            .then(response => {
                if (!response.ok) throw new Error("Selected JSON not found");
                return response.json();
            })
            .then(data => {
                selectedApps = data;
                renderSelectedList();
            })
            .catch(err => {
                console.error("Failed to load selected applications:", err);
                selectedApps = [];
                renderSelectedList();
            });
    }

    document.getElementById('save-btn').onclick = () => {
        fetch('./assets/save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(selectedApps)
        }).then(() => alert('Configuration saved.'));
    };

    document.getElementById('reset-btn').onclick = () => {
        fetch('./assets/reset.php', {
            method: 'POST'
        }).then(() => {
            selectedApps = [];
            renderSelectedList();
            alert('Configuration reset.');
        });
    };

    switchTab('deployment');
    fetchAvailableApps();
    fetchSelectedApps();
});