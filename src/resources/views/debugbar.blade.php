<!DOCTYPE html>
<div id="maharlika-debugbar" data-request-id="{{ $requestId }}" style="all: initial;">
    <style>
        /* Reset and Base Styles */
        #maharlika-debugbar * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        #maharlika-debugbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999999;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
        }

        /* Main Bar */
        .debugbar-main {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .debugbar-main:hover {
            box-shadow: 0 -4px 20px rgba(0,0,0,0.2);
        }

        .debugbar-tabs {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .debugbar-tab {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        .debugbar-tab:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        .debugbar-tab.active {
            background: white;
            color: #667eea;
        }

        .debugbar-badge {
            background: rgba(255, 255, 255, 0.3);
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
        }

        .debugbar-tab.active .debugbar-badge {
            background: #667eea;
            color: white;
        }

        .debugbar-controls {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .debugbar-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 12px;
        }

        .debugbar-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Panel */
        .debugbar-panel {
            background: white;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .debugbar-panel.open {
            max-height: 500px;
            overflow-y: auto;
            border-top: 1px solid #e2e8f0;
        }

        .debugbar-content {
            padding: 16px;
        }

        .debugbar-section {
            display: none;
        }

        .debugbar-section.active {
            display: block;
        }

        /* Tables */
        .debugbar-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .debugbar-table th {
            background: #f7fafc;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
        }

        .debugbar-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }

        .debugbar-table tr:last-child td {
            border-bottom: none;
        }

        .debugbar-table tr:hover {
            background: #f7fafc;
        }

        /* Code Blocks */
        .debugbar-code {
            background: #1a202c;
            color: #e2e8f0;
            padding: 12px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Monaco', 'Menlo', 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.6;
            margin-top: 8px;
        }

        /* Stats */
        .debugbar-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .debugbar-stat {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .debugbar-stat-label {
            font-size: 11px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .debugbar-stat-value {
            font-size: 24px;
            font-weight: bold;
            margin-top: 4px;
        }

        /* Logs */
        .debugbar-log {
            padding: 8px 12px;
            margin-bottom: 8px;
            border-left: 3px solid;
            border-radius: 4px;
            background: #f7fafc;
        }

        .debugbar-log.debug { border-color: #90cdf4; }
        .debugbar-log.info { border-color: #48bb78; }
        .debugbar-log.warning { border-color: #ed8936; }
        .debugbar-log.error { border-color: #f56565; }

        /* Scrollbar */
        .debugbar-panel::-webkit-scrollbar {
            width: 8px;
        }

        .debugbar-panel::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .debugbar-panel::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .debugbar-panel::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .debugbar-tabs {
                font-size: 11px;
            }
            .debugbar-tab {
                padding: 4px 8px;
            }
        }
    </style>

    <div class="debugbar-main" onclick="MaharlikaDebugbar.toggle()">
        <div class="debugbar-tabs">
            <div class="debugbar-tab active" onclick="event.stopPropagation(); MaharlikaDebugbar.showTab('time')">
                ⏱️ {{ $data['collectors']['time']['total'] ?? 0 }}ms
            </div>
            <div class="debugbar-tab" onclick="event.stopPropagation(); MaharlikaDebugbar.showTab('memory')">
                💾 {{ $data['collectors']['memory']['peak_formatted'] ?? '0 B' }}
            </div>
            <div class="debugbar-tab" onclick="event.stopPropagation(); MaharlikaDebugbar.showTab('database')">
                🗄️ {{ $data['collectors']['database']['total_queries'] ?? 0 }} <span class="debugbar-badge">{{ round($data['collectors']['database']['total_time'] ?? 0, 2) }}ms</span>
            </div>
            <div class="debugbar-tab" onclick="event.stopPropagation(); MaharlikaDebugbar.showTab('views')">
                👁️ {{ $data['collectors']['views']['total_views'] ?? 0 }} views
            </div>
            <div class="debugbar-tab" onclick="event.stopPropagation(); MaharlikaDebugbar.showTab('request')">
                📨 {{ $data['collectors']['request']['method'] ?? 'GET' }}
            </div>
            <div class="debugbar-tab" onclick="event.stopPropagation(); MaharlikaDebugbar.showTab('logs')">
                📋 {{ $data['collectors']['logs']['count'] ?? 0 }} logs
            </div>
        </div>
        <div class="debugbar-controls">
            <button class="debugbar-btn" onclick="event.stopPropagation(); MaharlikaDebugbar.clearStorage()">🗑️ Clear</button>
            <button class="debugbar-btn" onclick="event.stopPropagation(); MaharlikaDebugbar.toggle()">▼</button>
        </div>
    </div>

    <div class="debugbar-panel">
        <div class="debugbar-content">
            <!-- Time Section -->
            <div class="debugbar-section active" data-section="time">
                <h3 style="margin-bottom: 16px; color: #2d3748;">⏱️ Performance Metrics</h3>
                <div class="debugbar-stats">
                    <div class="debugbar-stat">
                        <div class="debugbar-stat-label">Total Time</div>
                        <div class="debugbar-stat-value">{{ $data['collectors']['time']['total'] ?? 0 }}ms</div>
                    </div>
                    <div class="debugbar-stat">
                        <div class="debugbar-stat-label">Peak Memory</div>
                        <div class="debugbar-stat-value">{{ $data['collectors']['memory']['peak_formatted'] ?? '0 B' }}</div>
                    </div>
                </div>
                @if(!empty($data['collectors']['time']['measures']))
                <table class="debugbar-table">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th style="text-align: right;">Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['collectors']['time']['measures'] as $measure)
                        <tr>
                            <td>{{ $measure['label'] }}</td>
                            <td style="text-align: right;">{{ $measure['duration'] }}ms</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            <!-- Memory Section -->
            <div class="debugbar-section" data-section="memory">
                <h3 style="margin-bottom: 16px; color: #2d3748;">💾 Memory Usage</h3>
                <div class="debugbar-stats">
                    <div class="debugbar-stat">
                        <div class="debugbar-stat-label">Current Memory</div>
                        <div class="debugbar-stat-value">{{ $data['collectors']['memory']['current_formatted'] ?? '0 B' }}</div>
                    </div>
                    <div class="debugbar-stat">
                        <div class="debugbar-stat-label">Peak Memory</div>
                        <div class="debugbar-stat-value">{{ $data['collectors']['memory']['peak_formatted'] ?? '0 B' }}</div>
                    </div>
                </div>
            </div>

            <!-- Database Section -->
            <div class="debugbar-section" data-section="database">
                <h3 style="margin-bottom: 16px; color: #2d3748;">🗄️ Database Queries</h3>
                <div class="debugbar-stats">
                    <div class="debugbar-stat">
                        <div class="debugbar-stat-label">Total Queries</div>
                        <div class="debugbar-stat-value">{{ $data['collectors']['database']['total_queries'] ?? 0 }}</div>
                    </div>
                    <div class="debugbar-stat">
                        <div class="debugbar-stat-label">Total Time</div>
                        <div class="debugbar-stat-value">{{ round($data['collectors']['database']['total_time'] ?? 0, 2) }}ms</div>
                    </div>
                </div>
                @if(!empty($data['collectors']['database']['queries']))
                <table class="debugbar-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>SQL</th>
                            <th style="text-align: right;">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['collectors']['database']['queries'] as $index => $query)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="debugbar-code" style="margin: 0;">{{ $query['formatted_sql'] }}</div>
                            </td>
                            <td style="text-align: right;">{{ $query['time'] }}ms</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            <!-- Views Section -->
            <div class="debugbar-section" data-section="views">
                <h3 style="margin-bottom: 16px; color: #2d3748;">👁️ Rendered Views</h3>
                @if(!empty($data['collectors']['views']['views']))
                <table class="debugbar-table">
                    <thead>
                        <tr>
                            <th>View</th>
                            <th>Path</th>
                            <th style="text-align: center;">Data Count</th>
                            <th style="text-align: right;">Render Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['collectors']['views']['views'] as $view)
                        <tr>
                            <td>{{ $view['name'] }}</td>
                            <td><code>{{ $view['path'] }}</code></td>
                            <td style="text-align: center;">{{ $view['data_count'] }}</td>
                            <td style="text-align: right;">{{ $view['render_time'] }}ms</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            <!-- Request Section -->
            <div class="debugbar-section" data-section="request">
                <h3 style="margin-bottom: 16px; color: #2d3748;">📨 Request Details</h3>
                <table class="debugbar-table">
                    <tr>
                        <th style="width: 200px;">Method</th>
                        <td>{{ $data['collectors']['request']['method'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>URI</th>
                        <td>{{ $data['collectors']['request']['uri'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>IP Address</th>
                        <td>{{ $data['collectors']['request']['ip'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>AJAX</th>
                        <td>{{ ($data['collectors']['request']['ajax'] ?? false) ? 'Yes' : 'No' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Logs Section -->
            <div class="debugbar-section" data-section="logs">
                <h3 style="margin-bottom: 16px; color: #2d3748;">📋 Application Logs</h3>
                @if(!empty($data['collectors']['logs']['logs']))
                    @foreach($data['collectors']['logs']['logs'] as $log)
                    <div class="debugbar-log {{ strtolower($log['level']) }}">
                        <strong>{{ $log['level'] }}:</strong> {{ $log['message'] }}
                    </div>
                    @endforeach
                @else
                    <p style="color: #718096;">No logs recorded for this request.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
window.MaharlikaDebugbar = {
    isOpen: false,
    currentTab: 'time',

    toggle: function() {
        const panel = document.querySelector('.debugbar-panel');
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            panel.classList.add('open');
        } else {
            panel.classList.remove('open');
        }
    },

    showTab: function(tabName) {
        // Update tabs
        document.querySelectorAll('.debugbar-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        event.currentTarget.classList.add('active');

        // Update sections
        document.querySelectorAll('.debugbar-section').forEach(section => {
            section.classList.remove('active');
        });
        document.querySelector(`[data-section="${tabName}"]`).classList.add('active');

        this.currentTab = tabName;

        // Open panel if closed
        if (!this.isOpen) {
            this.toggle();
        }
    },

    clearStorage: function() {
        if (confirm('Clear all debugbar data?')) {
            fetch('/_debugbar/clear', { method: 'POST' })
                .then(() => alert('Debugbar data cleared!'))
                .catch(() => alert('Failed to clear data'));
        }
    }
};
</script>