from flask import Flask, request, jsonify
import subprocess
import json
import os

app = Flask(__name__)

AWGCFG_EXECUTABLE = './dist/awgcfg'

if not os.path.isfile(AWGCFG_EXECUTABLE):
    raise FileNotFoundError(f"{AWGCFG_EXECUTABLE} not found. Please ensure the file exists and is executable.")

def run_awgcfg_command(options):
    try:
        result = subprocess.run([AWGCFG_EXECUTABLE] + options, capture_output=True, text=True, check=True)
        return jsonify(json.loads(result.stdout)), 200
    except subprocess.CalledProcessError as e:
        try:
            error_output = json.loads(e.stderr)
        except json.JSONDecodeError:
            error_output = {'error': 'Command failed', 'details': e.stderr}
        return jsonify(error_output), 500

@app.route('/add_peer', methods=['POST'])
def add_peer():
    data = request.json
    peer_name = data.get('peer_name')

    if not peer_name:
        return jsonify({'error': 'Missing required field: peer_name'}), 400

    return run_awgcfg_command(['--add', peer_name, '--json'])

@app.route('/update_peer', methods=['PUT'])
def update_peer():
    data = request.json
    peer_name = data.get('peer_name')
    new_data = data.get('new_data')

    if not (peer_name and new_data):
        return jsonify({'error': 'Missing required fields'}), 400

    return run_awgcfg_command(['--update', peer_name, '--data', json.dumps(new_data), '--json'])

@app.route('/delete_peer', methods=['DELETE'])
def delete_peer():
    data = request.json
    peer_name = data.get('peer_name')

    if not peer_name:
        return jsonify({'error': 'Missing required field: peer_name'}), 400

    return run_awgcfg_command(['--delete', peer_name, '--json'])

@app.route('/get_config', methods=['GET'])
def get_config():
    return run_awgcfg_command(['--get-config', '--json'])

@app.route('/create', methods=['POST'])
def create():
    data = request.json
    config_name = data.get('config_name')

    if not config_name:
        return jsonify({'error': 'Missing required field: config_name'}), 400

    return run_awgcfg_command(['--create', config_name, '--json'])

@app.route('/make', methods=['POST'])
def make():
    data = request.json
    config_name = data.get('config_name')

    if not config_name:
        return jsonify({'error': 'Missing required field: config_name'}), 400

    return run_awgcfg_command(['--make', config_name, '--json'])

@app.route('/tun', methods=['POST'])
def tun():
    data = request.json
    tun_name = data.get('tun_name')

    if not tun_name:
        return jsonify({'error': 'Missing required field: tun_name'}), 400

    return run_awgcfg_command(['--tun', tun_name, '--json'])

if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5000,debug=True)