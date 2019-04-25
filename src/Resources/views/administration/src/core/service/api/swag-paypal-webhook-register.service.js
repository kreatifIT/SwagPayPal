import ApiService from 'src/core/service/api.service';

class SwagPayPalWebhookRegisterService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'paypal') {
        super(httpClient, loginService, apiEndpoint);
    }

    registerWebhook() {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .post(`_action/${this.getApiBasePath()}/webhook/register`, {}, {
                headers
            })
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

export default SwagPayPalWebhookRegisterService;