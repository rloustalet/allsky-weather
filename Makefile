PYTHON = python3
PIP = pip3
SYSTEMD_DIR = /etc/systemd/system
INSTALL_DIR = /home/$(USER)/allsky

.PHONY: install
install: install_packages install_files install_service enable_service

.PHONY: install_packages
install_packages:
	sudo $(PIP) install flask flask_cors smbus bme680

.PHONY: install_files
install_files:
	sudo cp -r html $(INSTALL_DIR)/html
	sudo cp -r scripts $(INSTALL_DIR)/scripts

.PHONY: install_service
install_service:
	sudo cp weather.service $(SYSTEMD_DIR)

.PHONY: enable_service
enable_service:
	sudo systemctl enable weather.service

.PHONY: uninstall
uninstall:
	sudo systemctl disable weather.service
	sudo rm $(SYSTEMD_DIR)/weather.service
	sudo rm -rf $(INSTALL_DIR)

.PHONY: clean
clean:
	# Add any additional cleanup commands if needed
