import { APP_API_PROTOCOL, APP_API_URL, APP_API_PORT } from './config.js';

export default {
    delimiters: ['%{', '}%'],
    data() {
        return {
            organizationName: "",
            repositories: [],
            isSortByNameAsc: false,
            isSortByContributorsAsc: false,
            activeSort: "",
            loader: false,
            error: false,
            errorMessage: ""
        }
    },
    methods: {
        async findRepositories() {
            this.loader = true;
            this.repositories = [];
            this.resetError();
            await axios.post(`${APP_API_PROTOCOL}://${APP_API_URL}:${APP_API_PORT}/repositories`, { name: this.organizationName })
                .then(response => {
                    if (response.data.repositoriesInfo.length == 0) {
                        this.setError("Organizacja nie posiada publicznych repozytoriow");
                    } else {
                        this.repositories = response.data.repositoriesInfo;
                    }
                })
                .catch(error => {
                    if (error.response) {
                        if (error.response.data) {
                            console.log(error.response.data);
                            this.setError(error.response.data.errorMessage);
                        } else {
                            this.setError("Niespodziewany blad serwera");
                        }
                    } else if (error.request) {
                        this.setError("Brak polaczenia z serwerem");
                    } else {
                        this.setError("Niespodziewany blad serwera");
                    }
                });
            this.loader = false;
        },
        sortRepositoriesByName() {
            this.activeSort = "organizationName";
            this.isSortByNameAsc = !this.isSortByNameAsc;
            this.repositories.sort((a, b) => {
                if (this.isSortByNameAsc) {
                    return a.name.localeCompare(b.name);
                } else {
                    return b.name.localeCompare(a.name);
                }
            })
        },
        sortRepositoriesByContributors() {
            this.activeSort = "contributors";
            this.isSortByContributorsAsc = !this.isSortByContributorsAsc;
            this.repositories.sort((a, b) => {
                if (this.isSortByContributorsAsc) {
                    return a.contributorsNumber - b.contributorsNumber;
                } else {
                    return b.contributorsNumber - a.contributorsNumber;
                }
            })
        },
        resetError() {
            this.error = false;
            this.errorMessage = "";
        },
        setError(message) {
            this.error = true;
            this.errorMessage = message;
        }
    }
}
