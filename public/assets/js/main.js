import { APP_API_PROTOCOL, APP_API_URL, APP_API_PORT } from './config.js';

export default {
    delimiters: ['%{', '}%'],
    data() {
        return {
            organizationName: "",
            repositories: [],
            nameAsc: false,
            contributorsAsc: false,
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
            this.repositories.sort((a, b) => {
                if (this.nameAsc) {
                    return a.name.localeCompare(b.name);
                } else {
                    return b.name.localeCompare(a.name);
                }
            })
            this.nameAsc = !this.nameAsc;
        },
        sortRepositoriesByContributors() {
            this.activeSort = "contributors"
            this.repositories.sort((a, b) => {
                if (this.contributorsAsc) {
                    return a.contributorsNumber - b.contributorsNumber;
                } else {
                    return b.contributorsNumber - a.contributorsNumber;
                }
            })
            this.contributorsAsc = !this.contributorsAsc;
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
