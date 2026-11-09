(() => {
    const config = window.wasSiteWebMcp;
    const context = document.modelContext || navigator.modelContext;
    if (!config || typeof context?.registerTool !== 'function') {
        return;
    }

    for (const tool of config.tools) {
        const register = async () => {
            try {
                await context.registerTool({
                    name: tool.name,
                    description: tool.description,
                    inputSchema: tool.inputSchema,
                    annotations: { readOnlyHint: true },
                    async execute(input, { signal } = {}) {
                        const response = await fetch(config.endpoint, {
                            method: 'POST',
                            credentials: 'omit',
                            signal,
                            headers: {
                                Accept: 'application/json, text/event-stream',
                                'Content-Type': 'application/json',
                                'MCP-Protocol-Version': config.protocolVersion,
                                'Mcp-Method': 'tools/call',
                                'Mcp-Name': tool.name,
                            },
                            body: JSON.stringify({
                                jsonrpc: '2.0',
                                id: 1,
                                method: 'tools/call',
                                params: {
                                    name: tool.name,
                                    arguments: input,
                                    _meta: {
                                        'io.modelcontextprotocol/protocolVersion': config.protocolVersion,
                                        'io.modelcontextprotocol/clientInfo': { name: 'amigo-secreto-webmcp', version: '1.0.0' },
                                        'io.modelcontextprotocol/clientCapabilities': {},
                                    },
                                },
                            }),
                        });
                        if (!response.ok) {
                            throw new Error('Não foi possível consultar as informações públicas.');
                        }
                        const message = await response.json();
                        if (message.error) {
                            throw new Error(message.error.message);
                        }
                        return message.result;
                    },
                });
            } catch (error) {
                console.warn('Não foi possível registrar a ferramenta WebMCP.', tool.name, error);
            }
        };
        void register();
    }
})();
