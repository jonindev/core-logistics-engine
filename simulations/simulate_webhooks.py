import requests
import random
import time
import logging

# --- CONFIGURATION ---
TARGET_URL = "http://localhost:8080/api/webhooks/"
TOTAL_REQUESTS = 50  # Number of requests to send
CARRIERS = ["fedex", "ups", "dhl", "usps"]
STATUS_FLOW = ["pending", "in_transit", "delivered"]
# Pool of tracking numbers to simulate updates to existing shipments
TRACKING_POOL = [f"TRK{i:05d}" for i in range(1, 21)] 

# Configure Logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

def generate_random_payload(tracking_number, status):
    """Generates a variable payload for the shipment."""
    
    # Color mapping based on status
    color_map = {
        "pending": {"bg": "#E5E7EB", "text": "#374151"},     # Gray
        "in_transit": {"bg": "#DBEAFE", "text": "#1E40AF"},  # Blue
        "delivered": {"bg": "#D1FAE5", "text": "#065F46"},   # Green
    }
    colors = color_map.get(status, {"bg": "#FFFFFF", "text": "#000000"})

    payload = {
        "tracking_number": tracking_number,
        "status": status,
        "customer_name": f"Customer_{random.randint(1, 100)}",
        "color_bg": colors["bg"],
        "color_text": colors["text"]
    }

    # Randomly add extra attributes
    rand_val = random.random()
    if rand_val < 0.3:
        # Case 1: Weight only
        payload["weight"] = f"{random.randint(1, 50)}kg"
    elif rand_val < 0.6:
        # Case 2: Weight and Dimensions
        payload["weight"] = f"{random.randint(1, 50)}kg"
        payload["dimensions"] = f"{random.randint(10, 100)}x{random.randint(10, 100)}x{random.randint(10, 100)}cm"
    elif rand_val < 0.8:
        # Case 3: Internal Carrier Code only
        payload["internal_code"] = f"INT-{random.randint(1000, 9999)}"
    # Else: No extra attributes
    
    return payload

def simulate():
    logger.info(f"Starting simulation: sending {TOTAL_REQUESTS} requests...")
    
    # Keep track of the status of shipments in our pool to simulate a flow
    # Map: tracking_number -> current_status_index
    shipment_progress = {trk: 0 for trk in TRACKING_POOL}

    for i in range(1, TOTAL_REQUESTS + 1):
        # 1. Decide if we create a new shipment or update an existing one
        # 70% chance to use pool (update/progress), 30% chance for a brand new one
        if random.random() < 0.7:
            tracking_number = random.choice(TRACKING_POOL)
            # Progress the status if possible
            current_idx = shipment_progress[tracking_number]
            if current_idx < len(STATUS_FLOW) - 1:
                shipment_progress[tracking_number] += 1
            
            status = STATUS_FLOW[shipment_progress[tracking_number]]
            action = "Updating"
        else:
            tracking_number = f"NEW{random.randint(10000, 99999)}"
            status = STATUS_FLOW[0] # Always start with 'pending'
            action = "Creating"

        carrier = random.choice(CARRIERS)
        payload = generate_random_payload(tracking_number, status)
        url = f"{TARGET_URL}{carrier}"

        try:
            response = requests.post(url, json=payload, timeout=5)
            if response.status_code == 202:
                logger.info(f"[{i}/{TOTAL_REQUESTS}] {action} shipment {tracking_number} via {carrier} -> Status: {status} (OK)")
            else:
                logger.error(f"[{i}/{TOTAL_REQUESTS}] {action} shipment {tracking_number} via {carrier} -> Failed: {response.status_code}")
        except Exception as e:
            logger.error(f"[{i}/{TOTAL_REQUESTS}] Error sending request: {e}")

        # Random delay between 0.5 and 3 seconds to simulate real traffic
        time.sleep(random.uniform(0.5, 3.0))

    logger.info("Simulation completed.")

if __name__ == "__main__":
    simulate()
