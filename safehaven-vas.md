Get Services

# Get Services

Call this endpoint to fetch all the value added services provided by Safe Haven.

# OpenAPI definition

```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "Safe Haven IBS API",
    "version": "1.0"
  },
  "servers": [
    {
      "url": "https://api.sandbox.safehavenmfb.com"
    }
  ],
  "components": {
    "securitySchemes": {
      "sec0": {
        "type": "oauth2",
        "flows": {}
      }
    }
  },
  "security": [
    {
      "sec0": []
    }
  ],
  "paths": {
    "/vas/services": {
      "get": {
        "summary": "Get Services",
        "description": "Call this endpoint to fetch all the value added services provided by Safe Haven.",
        "operationId": "get-services",
        "parameters": [
          {
            "name": "ClientID",
            "in": "header",
            "description": "This is your 'ibs_client_id' returned in the response when you generate an api token",
            "required": true,
            "schema": {
              "type": "string"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "200",
            "content": {
              "application/json": {
                "examples": {
                  "Result": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Services fetched successfully.\",\n    \"data\": [\n        {\n            \"_id\": \"61e985180e69308aa37a7a94\",\n            \"name\": \"Mobile Recharge\",\n            \"identifier\": \"AIRTIME\",\n            \"description\": \"Airtime Recharge\",\n            \"createdAt\": \"2022-01-20T15:51:52.311Z\",\n            \"updatedAt\": \"2022-01-20T15:51:52.311Z\",\n            \"__v\": 0\n        },\n        {\n            \"_id\": \"61e9854bbce8e444a497663e\",\n            \"name\": \"DATA PURCHASE\",\n            \"identifier\": \"DATA\",\n            \"description\": \"Data bundle subscription\",\n            \"createdAt\": \"2022-01-20T15:52:43.385Z\",\n            \"updatedAt\": \"2022-01-20T15:52:43.385Z\",\n            \"__v\": 0\n        },\n        {\n            \"_id\": \"61e9857bbce8e444a4976641\",\n            \"name\": \"CABLE TV\",\n            \"identifier\": \"CABLETV\",\n            \"description\": \"Cable tv subscription\",\n            \"createdAt\": \"2022-01-20T15:53:31.994Z\",\n            \"updatedAt\": \"2022-01-20T15:53:31.994Z\",\n            \"__v\": 0\n        },\n        {\n            \"_id\": \"61e985a3bce8e444a4976643\",\n            \"name\": \"UTILITY BILLS\",\n            \"identifier\": \"UTILITY\",\n            \"description\": \"Power and Disco bills\",\n            \"createdAt\": \"2022-01-20T15:54:11.083Z\",\n            \"updatedAt\": \"2022-01-20T15:54:11.083Z\",\n            \"__v\": 0\n        }\n    ]\n}"
                  }
                },
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "integer",
                      "example": 200,
                      "default": 0
                    },
                    "message": {
                      "type": "string",
                      "example": "Services fetched successfully."
                    },
                    "data": {
                      "type": "array",
                      "items": {
                        "type": "object",
                        "properties": {
                          "_id": {
                            "type": "string",
                            "example": "61e985180e69308aa37a7a94"
                          },
                          "name": {
                            "type": "string",
                            "example": "Mobile Recharge"
                          },
                          "identifier": {
                            "type": "string",
                            "example": "AIRTIME"
                          },
                          "description": {
                            "type": "string",
                            "example": "Airtime Recharge"
                          },
                          "createdAt": {
                            "type": "string",
                            "example": "2022-01-20T15:51:52.311Z"
                          },
                          "updatedAt": {
                            "type": "string",
                            "example": "2022-01-20T15:51:52.311Z"
                          },
                          "__v": {
                            "type": "integer",
                            "example": 0,
                            "default": 0
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        },
        "deprecated": false
      }
    }
  },
  "x-readme": {
    "headers": [],
    "explorer-enabled": true,
    "proxy-enabled": true
  },
  "x-readme-fauxas": true,
  "_id": "61e69ed05b7bd4006a962eed:61f29099af2c40005d8c4800"
}
```

Get Service

# Get Service

This returns the object of the specified service using the id.

# OpenAPI definition

```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "Safe Haven IBS API",
    "version": "1.0"
  },
  "servers": [
    {
      "url": "https://api.sandbox.safehavenmfb.com"
    }
  ],
  "components": {
    "securitySchemes": {
      "sec0": {
        "type": "oauth2",
        "flows": {}
      }
    }
  },
  "security": [
    {
      "sec0": []
    }
  ],
  "paths": {
    "/vas/service/{id}": {
      "get": {
        "summary": "Get Service",
        "description": "This returns the object of the specified service using the id.",
        "operationId": "get-service",
        "parameters": [
          {
            "name": "id",
            "in": "path",
            "description": "The `_id` of the service you wish to get information on.",
            "schema": {
              "type": "string"
            },
            "required": true
          },
          {
            "name": "ClientID",
            "in": "header",
            "description": "This is your 'ibs_client_id' returned in the response when you generate an api token",
            "required": true,
            "schema": {
              "type": "string"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "200",
            "content": {
              "application/json": {
                "examples": {
                  "Airtime ": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service fetched successfully.\",\n    \"data\": {\n        \"_id\": \"61e985180e69308aa37a7a94\",\n        \"name\": \"Mobile Recharge\",\n        \"identifier\": \"AIRTIME\",\n        \"description\": \"Airtime Recharge\",\n        \"createdAt\": \"2022-01-20T15:51:52.311Z\",\n        \"updatedAt\": \"2022-01-20T15:51:52.311Z\",\n        \"__v\": 0\n    }\n}"
                  },
                  "Data Purchase": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service fetched successfully.\",\n    \"data\": {\n        \"_id\": \"61e9854bbce8e444a497663e\",\n        \"name\": \"DATA PURCHASE\",\n        \"identifier\": \"DATA\",\n        \"description\": \"Data bundle subscription\",\n        \"createdAt\": \"2022-01-20T15:52:43.385Z\",\n        \"updatedAt\": \"2022-01-20T15:52:43.385Z\",\n        \"__v\": 0\n    }\n}"
                  },
                  "Cable Tv": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service fetched successfully.\",\n    \"data\": {\n        \"_id\": \"61e9857bbce8e444a4976641\",\n        \"name\": \"CABLE TV\",\n        \"identifier\": \"CABLETV\",\n        \"description\": \"Cable tv subscription\",\n        \"createdAt\": \"2022-01-20T15:53:31.994Z\",\n        \"updatedAt\": \"2022-01-20T15:53:31.994Z\",\n        \"__v\": 0\n    }\n}"
                  },
                  "Utility Bills": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service fetched successfully.\",\n    \"data\": {\n        \"_id\": \"61e985a3bce8e444a4976643\",\n        \"name\": \"UTILITY BILLS\",\n        \"identifier\": \"UTILITY\",\n        \"description\": \"Power and Disco bills\",\n        \"createdAt\": \"2022-01-20T15:54:11.083Z\",\n        \"updatedAt\": \"2022-01-20T15:54:11.083Z\",\n        \"__v\": 0\n    }\n}"
                  }
                },
                "schema": {
                  "oneOf": [
                    {
                      "title": "Airtime ",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service fetched successfully."
                        },
                        "data": {
                          "type": "object",
                          "properties": {
                            "_id": {
                              "type": "string",
                              "example": "61e985180e69308aa37a7a94"
                            },
                            "name": {
                              "type": "string",
                              "example": "Mobile Recharge"
                            },
                            "identifier": {
                              "type": "string",
                              "example": "AIRTIME"
                            },
                            "description": {
                              "type": "string",
                              "example": "Airtime Recharge"
                            },
                            "createdAt": {
                              "type": "string",
                              "example": "2022-01-20T15:51:52.311Z"
                            },
                            "updatedAt": {
                              "type": "string",
                              "example": "2022-01-20T15:51:52.311Z"
                            },
                            "__v": {
                              "type": "integer",
                              "example": 0,
                              "default": 0
                            }
                          }
                        }
                      }
                    },
                    {
                      "title": "Data Purchase",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service fetched successfully."
                        },
                        "data": {
                          "type": "object",
                          "properties": {
                            "_id": {
                              "type": "string",
                              "example": "61e9854bbce8e444a497663e"
                            },
                            "name": {
                              "type": "string",
                              "example": "DATA PURCHASE"
                            },
                            "identifier": {
                              "type": "string",
                              "example": "DATA"
                            },
                            "description": {
                              "type": "string",
                              "example": "Data bundle subscription"
                            },
                            "createdAt": {
                              "type": "string",
                              "example": "2022-01-20T15:52:43.385Z"
                            },
                            "updatedAt": {
                              "type": "string",
                              "example": "2022-01-20T15:52:43.385Z"
                            },
                            "__v": {
                              "type": "integer",
                              "example": 0,
                              "default": 0
                            }
                          }
                        }
                      }
                    },
                    {
                      "title": "Cable Tv",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service fetched successfully."
                        },
                        "data": {
                          "type": "object",
                          "properties": {
                            "_id": {
                              "type": "string",
                              "example": "61e9857bbce8e444a4976641"
                            },
                            "name": {
                              "type": "string",
                              "example": "CABLE TV"
                            },
                            "identifier": {
                              "type": "string",
                              "example": "CABLETV"
                            },
                            "description": {
                              "type": "string",
                              "example": "Cable tv subscription"
                            },
                            "createdAt": {
                              "type": "string",
                              "example": "2022-01-20T15:53:31.994Z"
                            },
                            "updatedAt": {
                              "type": "string",
                              "example": "2022-01-20T15:53:31.994Z"
                            },
                            "__v": {
                              "type": "integer",
                              "example": 0,
                              "default": 0
                            }
                          }
                        }
                      }
                    },
                    {
                      "title": "Utility Bills",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service fetched successfully."
                        },
                        "data": {
                          "type": "object",
                          "properties": {
                            "_id": {
                              "type": "string",
                              "example": "61e985a3bce8e444a4976643"
                            },
                            "name": {
                              "type": "string",
                              "example": "UTILITY BILLS"
                            },
                            "identifier": {
                              "type": "string",
                              "example": "UTILITY"
                            },
                            "description": {
                              "type": "string",
                              "example": "Power and Disco bills"
                            },
                            "createdAt": {
                              "type": "string",
                              "example": "2022-01-20T15:54:11.083Z"
                            },
                            "updatedAt": {
                              "type": "string",
                              "example": "2022-01-20T15:54:11.083Z"
                            },
                            "__v": {
                              "type": "integer",
                              "example": 0,
                              "default": 0
                            }
                          }
                        }
                      }
                    }
                  ]
                }
              }
            }
          }
        },
        "deprecated": false
      }
    }
  },
  "x-readme": {
    "headers": [],
    "explorer-enabled": true,
    "proxy-enabled": true
  },
  "x-readme-fauxas": true,
  "_id": "61e69ed05b7bd4006a962eed:61f290ee8d3e68006823f939"
}
```Get Service Categories

# Get Service Categories

The endpoint returns all the categories of a specified service.

# OpenAPI definition

```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "Safe Haven IBS API",
    "version": "1.0"
  },
  "servers": [
    {
      "url": "https://api.sandbox.safehavenmfb.com"
    }
  ],
  "components": {
    "securitySchemes": {
      "sec0": {
        "type": "oauth2",
        "flows": {}
      }
    }
  },
  "security": [
    {
      "sec0": []
    }
  ],
  "paths": {
    "/vas/service/{id}/service-categories": {
      "get": {
        "summary": "Get Service Categories",
        "description": "The endpoint returns all the categories of a specified service.",
        "operationId": "get-service-categories",
        "parameters": [
          {
            "name": "id",
            "in": "path",
            "description": "The `_id` of the service.",
            "schema": {
              "type": "string"
            },
            "required": true
          },
          {
            "name": "ClientID",
            "in": "header",
            "description": "This is your 'ibs_client_id' returned in the response when you generate an api token",
            "required": true,
            "schema": {
              "type": "string"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "200",
            "content": {
              "application/json": {
                "examples": {
                  "Airtime": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service Categories fetched successfully.\",\n    \"data\": [\n        {\n            \"_id\": \"61e988830e69308aa37a7a99\",\n            \"name\": \"MTN\",\n            \"identifier\": \"MTN\",\n            \"service\": \"61e985180e69308aa37a7a94\",\n            \"vendor\": \"68075467e1c44b9bf8d23749\",\n            \"isFixedAmount\": false,\n            \"description\": \"Mtn Airtime\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646209736/SafeHavenVAS/New-mtn-logo_u1dy66.jpg\",\n            \"providerCommission\": {\n                \"flatAmount\": 0,\n                \"percentageAmount\": 3.5,\n                \"minCap\": null,\n                \"maxCap\": null\n            },\n            \"createdAt\": \"2022-01-20T16:06:27.339Z\",\n            \"updatedAt\": \"2025-07-21T12:18:34.967Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e9896abce8e444a4976648\",\n            \"name\": \"AIRTEL\",\n            \"identifier\": \"AIRTEL\",\n            \"service\": \"61e985180e69308aa37a7a94\",\n            \"vendor\": \"68075467e1c44b9bf8d23749\",\n            \"isFixedAmount\": false,\n            \"description\": \"Airtel Airtime\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/Airtel-Logo-Vector_1_hwmiic.png\",\n            \"createdAt\": \"2022-01-20T16:10:18.003Z\",\n            \"updatedAt\": \"2025-07-21T12:38:37.056Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e9898cbce8e444a497664b\",\n            \"name\": \"ETISALAT\",\n            \"identifier\": \"ETISALAT\",\n            \"service\": \"61e985180e69308aa37a7a94\",\n            \"vendor\": \"68075467e1c44b9bf8d23749\",\n            \"isFixedAmount\": false,\n            \"description\": \"Etisalat Airtime\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/download_1_y1c4lr.png\",\n            \"createdAt\": \"2022-01-20T16:10:52.982Z\",\n            \"updatedAt\": \"2025-07-21T12:38:14.353Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e9892e0e69308aa37a7a9c\",\n            \"name\": \"GLO\",\n            \"identifier\": \"GLO\",\n            \"service\": \"61e985180e69308aa37a7a94\",\n            \"vendor\": \"68075467e1c44b9bf8d23749\",\n            \"isFixedAmount\": false,\n            \"description\": \"Glo Airtime\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208567/SafeHavenVAS/kindpng_4648442_1_ibibke.png\",\n            \"createdAt\": \"2022-01-20T16:09:18.298Z\",\n            \"updatedAt\": \"2025-07-21T12:37:22.048Z\",\n            \"__v\": 0\n        }\n    ]\n}"
                  },
                  "Data": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service Categories fetched successfully.\",\n    \"data\": [\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e989f20e69308aa37a7a9f\",\n            \"name\": \"MTN\",\n            \"identifier\": \"MTN\",\n            \"service\": \"61e9854bbce8e444a497663e\",\n            \"vendor\": \"68075467e1c44b9bf8d23749\",\n            \"isFixedAmount\": true,\n            \"description\": \"Mtn Data Bundle\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646209736/SafeHavenVAS/New-mtn-logo_u1dy66.jpg\",\n            \"createdAt\": \"2022-01-20T16:12:34.059Z\",\n            \"updatedAt\": \"2025-04-29T14:10:01.061Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98a4dbce8e444a4976651\",\n            \"name\": \"AIRTEL\",\n            \"identifier\": \"AIRTEL\",\n            \"service\": \"61e9854bbce8e444a497663e\",\n            \"vendor\": \"68075467e1c44b9bf8d23749\",\n            \"isFixedAmount\": true,\n            \"description\": \"Airtel Data Bundle\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/Airtel-Logo-Vector_1_hwmiic.png\",\n            \"createdAt\": \"2022-01-20T16:14:05.341Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98a67bce8e444a4976654\",\n            \"name\": \"ETISALAT\",\n            \"identifier\": \"ETISALAT\",\n            \"service\": \"61e9854bbce8e444a497663e\",\n            \"vendor\": \"68075426ca511c95f50264e4\",\n            \"isFixedAmount\": true,\n            \"description\": \"Etisalat Data Bundle\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/download_1_y1c4lr.png\",\n            \"createdAt\": \"2022-01-20T16:14:31.228Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98a31bce8e444a497664e\",\n            \"name\": \"GLO\",\n            \"identifier\": \"GLO\",\n            \"service\": \"61e9854bbce8e444a497663e\",\n            \"vendor\": \"68075426ca511c95f50264e4\",\n            \"isFixedAmount\": true,\n            \"description\": \"Glo Data Bundle\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208567/SafeHavenVAS/kindpng_4648442_1_ibibke.png\",\n            \"createdAt\": \"2022-01-20T16:13:37.408Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"6502e9a236208a4de6d1b2ac\",\n            \"__v\": 0,\n            \"createdAt\": \"2022-01-20T16:12:34.059Z\",\n            \"description\": \"Mtn Data Bundle\",\n            \"identifier\": \"MTN-DATA\",\n            \"isFixedAmount\": true,\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646209736/SafeHavenVAS/New-mtn-logo_u1dy66.jpg\",\n            \"name\": \"MTN-COOPERATE-DATA\",\n            \"service\": \"61e9854bbce8e444a497663e\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"vendor\": \"68075426ca511c95f50264e4\"\n        }\n    ]\n}"
                  },
                  "Cable Tv": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service Categories fetched successfully.\",\n    \"data\": [\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98b3562d9b6a917f9302a\",\n            \"name\": \"DSTV BILL\",\n            \"identifier\": \"DSTV\",\n            \"service\": \"61e9857bbce8e444a4976641\",\n            \"vendor\": \"68ab156745ab33895381ec86\",\n            \"isFixedAmount\": true,\n            \"description\": \"DSTV Bundle\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/57-579840_dstv-logo_1_jsxrmc.png\",\n            \"createdAt\": \"2022-01-20T16:17:57.938Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98b6462d9b6a917f9302d\",\n            \"name\": \"GOTV\",\n            \"identifier\": \"GOTV\",\n            \"service\": \"61e9857bbce8e444a4976641\",\n            \"vendor\": \"68ab156745ab33895381ec86\",\n            \"isFixedAmount\": true,\n            \"description\": \"GOTV Package\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/gotv_1_tbbz8u.png\",\n            \"createdAt\": \"2022-01-20T16:18:44.195Z\",\n            \"updatedAt\": \"2025-07-21T12:43:42.593Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98b8a90dbbfc905f48f07\",\n            \"name\": \"STARTIMES\",\n            \"identifier\": \"STARTIMES\",\n            \"service\": \"61e9857bbce8e444a4976641\",\n            \"vendor\": \"68ab156745ab33895381ec86\",\n            \"isFixedAmount\": true,\n            \"description\": \"STARTIMES Package\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208568/SafeHavenVAS/startimes_logo_1_eqhjpf.png\",\n            \"createdAt\": \"2022-01-20T16:19:22.033Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        }\n    ]\n}"
                  },
                  "Utility Bills": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service Categories fetched successfully.\",\n    \"data\": [\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98bf962d9b6a917f93030\",\n            \"name\": \"BEDC\",\n            \"identifier\": \"BENIN\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68075426ca511c95f50264e4\",\n            \"isFixedAmount\": false,\n            \"description\": \"Benin Electricity Distribution Company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/bedc_transparent_logo_1_vortpx.png\",\n            \"createdAt\": \"2022-01-20T16:21:13.866Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98c2090dbbfc905f48f0a\",\n            \"name\": \"EKEDC\",\n            \"identifier\": \"EKO\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68ab156745ab33895381ec86\",\n            \"isFixedAmount\": false,\n            \"description\": \"Eko Electricity Distribution Company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208567/SafeHavenVAS/ekedc_logo_1_gvanzy.png\",\n            \"createdAt\": \"2022-01-20T16:21:52.453Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98c4390dbbfc905f48f0d\",\n            \"name\": \"AEDC\",\n            \"identifier\": \"ABUJA\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68ab156745ab33895381ec86\",\n            \"isFixedAmount\": false,\n            \"description\": \"Abuja Electricity Distribution Company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/aedc_logo_1_u4foxr.png\",\n            \"createdAt\": \"2022-01-20T16:22:27.741Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98c5f90dbbfc905f48f10\",\n            \"name\": \"EEDC\",\n            \"identifier\": \"ENUGU\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68ab156745ab33895381ec86\",\n            \"isFixedAmount\": false,\n            \"description\": \"Enugu Electricity Distribution Company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208566/SafeHavenVAS/eedc_logo_1_pggere.png\",\n            \"createdAt\": \"2022-01-20T16:22:55.700Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98c7790dbbfc905f48f13\",\n            \"name\": \"IBEDC\",\n            \"identifier\": \"IBADAN\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68ab156745ab33895381ec86\",\n            \"isFixedAmount\": false,\n            \"description\": \"Ibadan Electricity Distribution Company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208568/SafeHavenVAS/ibedc_1_zquagj.png\",\n            \"createdAt\": \"2022-01-20T16:23:19.812Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98ca5ab69182f326e6db6\",\n            \"name\": \"IKEDC\",\n            \"identifier\": \"IKEJA\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68075426ca511c95f50264e4\",\n            \"isFixedAmount\": false,\n            \"description\": \"Ikeja Electricity Distribution Company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208566/SafeHavenVAS/Ikeja-Electric-Logo-new-1_1_xzufx0.png\",\n            \"createdAt\": \"2022-01-20T16:24:05.663Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98cc6ab69182f326e6db9\",\n            \"name\": \"JEDC\",\n            \"identifier\": \"JOS\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68075426ca511c95f50264e4\",\n            \"isFixedAmount\": false,\n            \"description\": \"JOS Electricity Distribution Company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208567/SafeHavenVAS/Jos-Electricity-Distribution-Company_1_ybqwmz.png\",\n            \"createdAt\": \"2022-01-20T16:24:38.690Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98ceeab69182f326e6dbc\",\n            \"name\": \"KAEDC\",\n            \"identifier\": \"KADUNA\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68075426ca511c95f50264e4\",\n            \"isFixedAmount\": false,\n            \"description\": \"Kaduna Electricity Distribution Company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/34-341783_kaduna-electricity-distribution-company-kaduna-electricity-distribution-company_1_cvxnol.png\",\n            \"createdAt\": \"2022-01-20T16:25:18.310Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98d0bab69182f326e6dbf\",\n            \"name\": \"KEDCO\",\n            \"identifier\": \"KANO\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68075426ca511c95f50264e4\",\n            \"isFixedAmount\": false,\n            \"description\": \"Kano Electricity Distribution Company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208567/SafeHavenVAS/Kedco_Logo_web_1_hbbhfj.png\",\n            \"createdAt\": \"2022-01-20T16:25:47.379Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98d28ab69182f326e6dc2\",\n            \"name\": \"PHEDC\",\n            \"identifier\": \"PORTHARCOURT\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68075426ca511c95f50264e4\",\n            \"isFixedAmount\": false,\n            \"description\": \"PortHarcourt Electricity Distribution Company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208567/SafeHavenVAS/PHED_1_rpevjz.png\",\n            \"createdAt\": \"2022-01-20T16:26:16.079Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        },\n        {\n            \"providerCommission\": null,\n            \"_id\": \"61e98d43ab69182f326e6dc5\",\n            \"name\": \"YEDC\",\n            \"identifier\": \"YOLA\",\n            \"service\": \"61e985a3bce8e444a4976643\",\n            \"vendor\": \"68075426ca511c95f50264e4\",\n            \"isFixedAmount\": false,\n            \"description\": \"Yola Electricity distribution company\",\n            \"logoUrl\": \"https://res.cloudinary.com/sudo-africa/image/upload/v1646208568/SafeHavenVAS/yedc_logo_2_cgumad.png\",\n            \"createdAt\": \"2022-01-20T16:26:43.361Z\",\n            \"updatedAt\": \"2025-04-22T08:58:16.891Z\",\n            \"__v\": 0\n        }\n    ]\n}"
                  }
                },
                "schema": {
                  "oneOf": [
                    {
                      "title": "Airtime",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service Categories fetched successfully."
                        },
                        "data": {
                          "type": "array",
                          "items": {
                            "type": "object",
                            "properties": {
                              "_id": {
                                "type": "string",
                                "example": "61e988830e69308aa37a7a99"
                              },
                              "name": {
                                "type": "string",
                                "example": "MTN"
                              },
                              "identifier": {
                                "type": "string",
                                "example": "MTN"
                              },
                              "service": {
                                "type": "string",
                                "example": "61e985180e69308aa37a7a94"
                              },
                              "vendor": {
                                "type": "string",
                                "example": "68075467e1c44b9bf8d23749"
                              },
                              "isFixedAmount": {
                                "type": "boolean",
                                "example": false,
                                "default": true
                              },
                              "description": {
                                "type": "string",
                                "example": "Mtn Airtime"
                              },
                              "logoUrl": {
                                "type": "string",
                                "example": "https://res.cloudinary.com/sudo-africa/image/upload/v1646209736/SafeHavenVAS/New-mtn-logo_u1dy66.jpg"
                              },
                              "providerCommission": {
                                "type": "object",
                                "properties": {
                                  "flatAmount": {
                                    "type": "integer",
                                    "example": 0,
                                    "default": 0
                                  },
                                  "percentageAmount": {
                                    "type": "number",
                                    "example": 3.5,
                                    "default": 0
                                  },
                                  "minCap": {},
                                  "maxCap": {}
                                }
                              },
                              "createdAt": {
                                "type": "string",
                                "example": "2022-01-20T16:06:27.339Z"
                              },
                              "updatedAt": {
                                "type": "string",
                                "example": "2025-07-21T12:18:34.967Z"
                              },
                              "__v": {
                                "type": "integer",
                                "example": 0,
                                "default": 0
                              }
                            }
                          }
                        }
                      }
                    },
                    {
                      "title": "Data",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service Categories fetched successfully."
                        },
                        "data": {
                          "type": "array",
                          "items": {
                            "type": "object",
                            "properties": {
                              "providerCommission": {},
                              "_id": {
                                "type": "string",
                                "example": "61e989f20e69308aa37a7a9f"
                              },
                              "name": {
                                "type": "string",
                                "example": "MTN"
                              },
                              "identifier": {
                                "type": "string",
                                "example": "MTN"
                              },
                              "service": {
                                "type": "string",
                                "example": "61e9854bbce8e444a497663e"
                              },
                              "vendor": {
                                "type": "string",
                                "example": "68075467e1c44b9bf8d23749"
                              },
                              "isFixedAmount": {
                                "type": "boolean",
                                "example": true,
                                "default": true
                              },
                              "description": {
                                "type": "string",
                                "example": "Mtn Data Bundle"
                              },
                              "logoUrl": {
                                "type": "string",
                                "example": "https://res.cloudinary.com/sudo-africa/image/upload/v1646209736/SafeHavenVAS/New-mtn-logo_u1dy66.jpg"
                              },
                              "createdAt": {
                                "type": "string",
                                "example": "2022-01-20T16:12:34.059Z"
                              },
                              "updatedAt": {
                                "type": "string",
                                "example": "2025-04-29T14:10:01.061Z"
                              },
                              "__v": {
                                "type": "integer",
                                "example": 0,
                                "default": 0
                              }
                            }
                          }
                        }
                      }
                    },
                    {
                      "title": "Cable Tv",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service Categories fetched successfully."
                        },
                        "data": {
                          "type": "array",
                          "items": {
                            "type": "object",
                            "properties": {
                              "providerCommission": {},
                              "_id": {
                                "type": "string",
                                "example": "61e98b3562d9b6a917f9302a"
                              },
                              "name": {
                                "type": "string",
                                "example": "DSTV BILL"
                              },
                              "identifier": {
                                "type": "string",
                                "example": "DSTV"
                              },
                              "service": {
                                "type": "string",
                                "example": "61e9857bbce8e444a4976641"
                              },
                              "vendor": {
                                "type": "string",
                                "example": "68ab156745ab33895381ec86"
                              },
                              "isFixedAmount": {
                                "type": "boolean",
                                "example": true,
                                "default": true
                              },
                              "description": {
                                "type": "string",
                                "example": "DSTV Bundle"
                              },
                              "logoUrl": {
                                "type": "string",
                                "example": "https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/57-579840_dstv-logo_1_jsxrmc.png"
                              },
                              "createdAt": {
                                "type": "string",
                                "example": "2022-01-20T16:17:57.938Z"
                              },
                              "updatedAt": {
                                "type": "string",
                                "example": "2025-04-22T08:58:16.891Z"
                              },
                              "__v": {
                                "type": "integer",
                                "example": 0,
                                "default": 0
                              }
                            }
                          }
                        }
                      }
                    },
                    {
                      "title": "Utility Bills",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service Categories fetched successfully."
                        },
                        "data": {
                          "type": "array",
                          "items": {
                            "type": "object",
                            "properties": {
                              "providerCommission": {},
                              "_id": {
                                "type": "string",
                                "example": "61e98bf962d9b6a917f93030"
                              },
                              "name": {
                                "type": "string",
                                "example": "BEDC"
                              },
                              "identifier": {
                                "type": "string",
                                "example": "BENIN"
                              },
                              "service": {
                                "type": "string",
                                "example": "61e985a3bce8e444a4976643"
                              },
                              "vendor": {
                                "type": "string",
                                "example": "68075426ca511c95f50264e4"
                              },
                              "isFixedAmount": {
                                "type": "boolean",
                                "example": false,
                                "default": true
                              },
                              "description": {
                                "type": "string",
                                "example": "Benin Electricity Distribution Company"
                              },
                              "logoUrl": {
                                "type": "string",
                                "example": "https://res.cloudinary.com/sudo-africa/image/upload/v1646208565/SafeHavenVAS/bedc_transparent_logo_1_vortpx.png"
                              },
                              "createdAt": {
                                "type": "string",
                                "example": "2022-01-20T16:21:13.866Z"
                              },
                              "updatedAt": {
                                "type": "string",
                                "example": "2025-04-22T08:58:16.891Z"
                              },
                              "__v": {
                                "type": "integer",
                                "example": 0,
                                "default": 0
                              }
                            }
                          }
                        }
                      }
                    }
                  ]
                }
              }
            }
          }
        },
        "deprecated": false
      }
    }
  },
  "x-readme": {
    "headers": [],
    "explorer-enabled": true,
    "proxy-enabled": true
  },
  "x-readme-fauxas": true,
  "_id": "61e69ed05b7bd4006a962eed:61f2914113035600554cdcf7"
}
```Get Category Products

# Get Category Products

This endpoint returns all the available products and offers under a specific category.

# OpenAPI definition

```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "Safe Haven IBS API",
    "version": "1.0"
  },
  "servers": [
    {
      "url": "https://api.sandbox.safehavenmfb.com"
    }
  ],
  "components": {
    "securitySchemes": {
      "sec0": {
        "type": "oauth2",
        "flows": {}
      }
    }
  },
  "security": [
    {
      "sec0": []
    }
  ],
  "paths": {
    "/vas/service-category/{id}/products": {
      "get": {
        "summary": "Get Category Products",
        "description": "This endpoint returns all the available products and offers under a specific category.",
        "operationId": "get-category-products",
        "parameters": [
          {
            "name": "id",
            "in": "path",
            "description": "The `_id` of the category.",
            "schema": {
              "type": "string"
            },
            "required": true
          },
          {
            "name": "ClientID",
            "in": "header",
            "description": "This is your 'ibs_client_id' returned in the response when you generate an api token",
            "required": true,
            "schema": {
              "type": "string"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "200",
            "content": {
              "application/json": {
                "examples": {
                  "Gotv": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service Category Products fetched successfully.\",\n    \"data\": [\n        {\n            \"name\": \"Gotv Supa+\",\n            \"bundleCode\": \"gb1\",\n            \"amount\": 13900,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"Gotv Supa\",\n            \"bundleCode\": \"gb2\",\n            \"amount\": 9600,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"Gotv Max\",\n            \"bundleCode\": \"gb3\",\n            \"amount\": 7200,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"GOtv Jolli\",\n            \"bundleCode\": \"gb4\",\n            \"amount\": 4850,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"GOtv Jinja\",\n            \"bundleCode\": \"gb5\",\n            \"amount\": 3300,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"GOtv Smallie\",\n            \"bundleCode\": \"gb6\",\n            \"amount\": 1575,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"GOtv Open\",\n            \"bundleCode\": \"gb7\",\n            \"amount\": 6600,\n            \"duration\": \"1 one-time\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"Top Up\",\n            \"bundleCode\": \"TOP_UP\",\n            \"amount\": null,\n            \"isAmountFixed\": false\n        }\n    ]\n}"
                  },
                  "Dstv": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service Category Products fetched successfully.\",\n    \"data\": [\n        {\n            \"name\": \"DStv Padi\",\n            \"bundleCode\": \"db1\",\n            \"amount\": 3600,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Padi with DStv Movies\",\n            \"bundleCode\": \"db1|da1\",\n            \"amount\": 6100,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Padi with DStv French Plus\",\n            \"bundleCode\": \"db1|da2\",\n            \"amount\": 24100,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Padi with DStv French Touch\",\n            \"bundleCode\": \"db1|da3\",\n            \"amount\": 9400,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Padi with DStv Indian\",\n            \"bundleCode\": \"db1|da4\",\n            \"amount\": 16000,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Padi with DStv Great Wall\",\n            \"bundleCode\": \"db1|da5\",\n            \"amount\": 6725,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Padi with DStv HDPVR Access Service\",\n            \"bundleCode\": \"db1|da6\",\n            \"amount\": 8600,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Padi with DStv Mobile\",\n            \"bundleCode\": \"db1|da7\",\n            \"amount\": 4390,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Yanga\",\n            \"bundleCode\": \"db2\",\n            \"amount\": 5100,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Yanga with DStv Movies\",\n            \"bundleCode\": \"db2|da1\",\n            \"amount\": 7600,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Yanga with DStv French Plus\",\n            \"bundleCode\": \"db2|da2\",\n            \"amount\": 25600,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Yanga with DStv French Touch\",\n            \"bundleCode\": \"db2|da3\",\n            \"amount\": 10900,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Yanga with DStv Indian\",\n            \"bundleCode\": \"db2|da4\",\n            \"amount\": 17500,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Yanga with DStv Great Wall\",\n            \"bundleCode\": \"db2|da5\",\n            \"amount\": 8225,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Yanga with DStv HDPVR Access Service\",\n            \"bundleCode\": \"db2|da6\",\n            \"amount\": 10100,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Yanga with DStv Mobile\",\n            \"bundleCode\": \"db2|da7\",\n            \"amount\": 5890,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Confam\",\n            \"bundleCode\": \"db3\",\n            \"amount\": 9300,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Confam with DStv Movies\",\n            \"bundleCode\": \"db3|da1\",\n            \"amount\": 11800,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Confam with DStv French Plus\",\n            \"bundleCode\": \"db3|da2\",\n            \"amount\": 29800,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Confam with DStv French Touch\",\n            \"bundleCode\": \"db3|da3\",\n            \"amount\": 15100,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Confam with DStv Indian\",\n            \"bundleCode\": \"db3|da4\",\n            \"amount\": 21700,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Confam with DStv Great Wall\",\n            \"bundleCode\": \"db3|da5\",\n            \"amount\": 12425,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Confam with DStv HDPVR Access Service\",\n            \"bundleCode\": \"db3|da6\",\n            \"amount\": 14300,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Confam with DStv Mobile\",\n            \"bundleCode\": \"db3|da7\",\n            \"amount\": 10090,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact\",\n            \"bundleCode\": \"db4\",\n            \"amount\": 15700,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact with DStv Movies\",\n            \"bundleCode\": \"db4|da1\",\n            \"amount\": 18200,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact with DStv French Plus\",\n            \"bundleCode\": \"db4|da2\",\n            \"amount\": 36200,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact with DStv French Touch\",\n            \"bundleCode\": \"db4|da3\",\n            \"amount\": 21500,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact with DStv Indian\",\n            \"bundleCode\": \"db4|da4\",\n            \"amount\": 28100,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact with DStv Great Wall\",\n            \"bundleCode\": \"db4|da5\",\n            \"amount\": 18825,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact with DStv HDPVR Access Service\",\n            \"bundleCode\": \"db4|da6\",\n            \"amount\": 20700,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact with DStv Mobile\",\n            \"bundleCode\": \"db4|da7\",\n            \"amount\": 16490,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact Plus\",\n            \"bundleCode\": \"db5\",\n            \"amount\": 25000,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact Plus with DStv Movies\",\n            \"bundleCode\": \"db5|da1\",\n            \"amount\": 27500,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact Plus with DStv French Plus\",\n            \"bundleCode\": \"db5|da2\",\n            \"amount\": 45500,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact Plus with DStv French Touch\",\n            \"bundleCode\": \"db5|da3\",\n            \"amount\": 30800,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact Plus with DStv Indian\",\n            \"bundleCode\": \"db5|da4\",\n            \"amount\": 37400,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact Plus with DStv Great Wall\",\n            \"bundleCode\": \"db5|da5\",\n            \"amount\": 28125,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact Plus with DStv HDPVR Access Service\",\n            \"bundleCode\": \"db5|da6\",\n            \"amount\": 30000,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Compact Plus with DStv Mobile\",\n            \"bundleCode\": \"db5|da7\",\n            \"amount\": 25790,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Premium\",\n            \"bundleCode\": \"db6\",\n            \"amount\": 37000,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Premium with DStv Movies\",\n            \"bundleCode\": \"db6|da1\",\n            \"amount\": 39500,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Premium with DStv French Plus\",\n            \"bundleCode\": \"db6|da2\",\n            \"amount\": 57500,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Premium with DStv French Touch\",\n            \"bundleCode\": \"db6|da3\",\n            \"amount\": 42800,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Premium with DStv Indian\",\n            \"bundleCode\": \"db6|da4\",\n            \"amount\": 49400,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Premium with DStv Great Wall\",\n            \"bundleCode\": \"db6|da5\",\n            \"amount\": 40125,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Premium with DStv HDPVR Access Service\",\n            \"bundleCode\": \"db6|da6\",\n            \"amount\": 42000,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"DStv Premium with DStv Mobile\",\n            \"bundleCode\": \"db6|da7\",\n            \"amount\": 37790,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"Top Up\",\n            \"bundleCode\": \"TOP_UP\",\n            \"amount\": null,\n            \"isAmountFixed\": false\n        }\n    ]\n}"
                  },
                  "Startimes": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Service Category Products fetched successfully.\",\n    \"data\": [\n        {\n            \"name\": \"StarTimes Nova (Antenna)\",\n            \"bundleCode\": \"sb1\",\n            \"amount\": 600,\n            \"duration\": \"1 week\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Nova (Antenna)\",\n            \"bundleCode\": \"sb2\",\n            \"amount\": 1900,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Basic (Antenna)\",\n            \"bundleCode\": \"sb3\",\n            \"amount\": 1250,\n            \"duration\": \"1 week\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Basic (Antenna)\",\n            \"bundleCode\": \"sb4\",\n            \"amount\": 3700,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Classic (Antenna)\",\n            \"bundleCode\": \"sb5\",\n            \"amount\": 1900,\n            \"duration\": \"1 week\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Classic (Antenna)\",\n            \"bundleCode\": \"sb6\",\n            \"amount\": 5500,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Super (Antenna)\",\n            \"bundleCode\": \"sb7\",\n            \"amount\": 3000,\n            \"duration\": \"1 week\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Super (Antenna)\",\n            \"bundleCode\": \"sb8\",\n            \"amount\": 8800,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Nova (Dish)\",\n            \"bundleCode\": \"sb9\",\n            \"amount\": 650,\n            \"duration\": \"1 week\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Nova (Dish)\",\n            \"bundleCode\": \"sb10\",\n            \"amount\": 1900,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Basic (Dish)\",\n            \"bundleCode\": \"sb11\",\n            \"amount\": 1550,\n            \"duration\": \"1 week\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Basic (Dish)\",\n            \"bundleCode\": \"sb12\",\n            \"amount\": 4700,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Classic (Dish)\",\n            \"bundleCode\": \"sb13\",\n            \"amount\": 2300,\n            \"duration\": \"1 week\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Classic (Dish)\",\n            \"bundleCode\": \"sb14\",\n            \"amount\": 6800,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Super (Dish)\",\n            \"bundleCode\": \"sb15\",\n            \"amount\": 3000,\n            \"duration\": \"1 week\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Super (Dish)\",\n            \"bundleCode\": \"sb16\",\n            \"amount\": 9000,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"StarTimes Chinese (Dish)\",\n            \"bundleCode\": \"sb17\",\n            \"amount\": 19000,\n            \"duration\": \"1 month\",\n            \"isAmountFixed\": true\n        },\n        {\n            \"name\": \"Top Up\",\n            \"bundleCode\": \"TOP_UP\",\n            \"amount\": null,\n            \"isAmountFixed\": false\n        }\n    ]\n}"
                  }
                },
                "schema": {
                  "oneOf": [
                    {
                      "title": "Gotv",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service Category Products fetched successfully."
                        },
                        "data": {
                          "type": "array",
                          "items": {
                            "type": "object",
                            "properties": {
                              "name": {
                                "type": "string",
                                "example": "Gotv Supa+"
                              },
                              "bundleCode": {
                                "type": "string",
                                "example": "gb1"
                              },
                              "amount": {
                                "type": "integer",
                                "example": 13900,
                                "default": 0
                              },
                              "duration": {
                                "type": "string",
                                "example": "1 month"
                              },
                              "isAmountFixed": {
                                "type": "boolean",
                                "example": true,
                                "default": true
                              }
                            }
                          }
                        }
                      }
                    },
                    {
                      "title": "Dstv",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service Category Products fetched successfully."
                        },
                        "data": {
                          "type": "array",
                          "items": {
                            "type": "object",
                            "properties": {
                              "name": {
                                "type": "string",
                                "example": "DStv Padi"
                              },
                              "bundleCode": {
                                "type": "string",
                                "example": "db1"
                              },
                              "amount": {
                                "type": "integer",
                                "example": 3600,
                                "default": 0
                              },
                              "duration": {
                                "type": "string",
                                "example": "1 month"
                              },
                              "isAmountFixed": {
                                "type": "boolean",
                                "example": true,
                                "default": true
                              }
                            }
                          }
                        }
                      }
                    },
                    {
                      "title": "Startimes",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Service Category Products fetched successfully."
                        },
                        "data": {
                          "type": "array",
                          "items": {
                            "type": "object",
                            "properties": {
                              "name": {
                                "type": "string",
                                "example": "StarTimes Nova (Antenna)"
                              },
                              "bundleCode": {
                                "type": "string",
                                "example": "sb1"
                              },
                              "amount": {
                                "type": "integer",
                                "example": 600,
                                "default": 0
                              },
                              "duration": {
                                "type": "string",
                                "example": "1 week"
                              },
                              "isAmountFixed": {
                                "type": "boolean",
                                "example": true,
                                "default": true
                              }
                            }
                          }
                        }
                      }
                    }
                  ]
                }
              }
            }
          }
        },
        "deprecated": false
      }
    }
  },
  "x-readme": {
    "headers": [],
    "explorer-enabled": true,
    "proxy-enabled": true
  },
  "x-readme-fauxas": true,
  "_id": "61e69ed05b7bd4006a962eed:61f29171f1939300728a8eea"
}
```Verify Power/Cable TV data

# Verify Power/Cable TV data

This verifies the provided power or cable tv data and returns more information on it if it is valid.

# OpenAPI definition

```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "Safe Haven IBS API",
    "version": "1.0"
  },
  "servers": [
    {
      "url": "https://api.sandbox.safehavenmfb.com"
    }
  ],
  "components": {
    "securitySchemes": {
      "sec0": {
        "type": "oauth2",
        "flows": {}
      }
    }
  },
  "security": [
    {
      "sec0": []
    }
  ],
  "paths": {
    "/vas/verify": {
      "post": {
        "summary": "Verify Power/Cable TV data",
        "description": "This verifies the provided power or cable tv data and returns more information on it if it is valid.",
        "operationId": "verify-powercable-tv-data",
        "parameters": [
          {
            "name": "ClientID",
            "in": "header",
            "description": "This is your 'ibs_client_id' returned in the response when you generate an api token",
            "required": true,
            "schema": {
              "type": "string"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "serviceCategoryId",
                  "entityNumber"
                ],
                "properties": {
                  "serviceCategoryId": {
                    "type": "string",
                    "description": "The `_id` of the service category."
                  },
                  "entityNumber": {
                    "type": "string",
                    "description": "This is the Card number for cable tv and the Meter number for utility"
                  }
                }
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "200",
            "content": {
              "application/json": {
                "examples": {
                  "Power": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Power Data verified successfully.\",\n    \"data\": {\n        \"discoCode\": \"ABUJA\",\n        \"vendType\": \"PREPAID\",\n        \"meterNo\": \"70338736519\",\n        \"minVendAmount\": 500,\n        \"maxVendAmount\": 1000000,\n        \"outstanding\": 13990.12,\n        \"debtRepayment\": 0,\n        \"name\": \"Shamsuddeen Omacy\",\n        \"address\": \"Adetekunbo Ademola Crs. Wuse 2\",\n        \"orderId\": \"AF193766EBFDE191B9AB04CD970419ZZ\"\n    }\n}"
                  },
                  "Cable Tv": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Cable TV number verified successfully.\",\n    \"sessionId\": \"090286251125033820106691997841\",\n    \"data\": {\n        \"name\": \"John Doe\"\n    }\n}"
                  }
                },
                "schema": {
                  "oneOf": [
                    {
                      "title": "Power",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Power Data verified successfully."
                        },
                        "data": {
                          "type": "object",
                          "properties": {
                            "discoCode": {
                              "type": "string",
                              "example": "ABUJA"
                            },
                            "vendType": {
                              "type": "string",
                              "example": "PREPAID"
                            },
                            "meterNo": {
                              "type": "string",
                              "example": "70338736519"
                            },
                            "minVendAmount": {
                              "type": "integer",
                              "example": 500,
                              "default": 0
                            },
                            "maxVendAmount": {
                              "type": "integer",
                              "example": 1000000,
                              "default": 0
                            },
                            "outstanding": {
                              "type": "number",
                              "example": 13990.12,
                              "default": 0
                            },
                            "debtRepayment": {
                              "type": "integer",
                              "example": 0,
                              "default": 0
                            },
                            "name": {
                              "type": "string",
                              "example": "Shamsuddeen Omacy"
                            },
                            "address": {
                              "type": "string",
                              "example": "Adetekunbo Ademola Crs. Wuse 2"
                            },
                            "orderId": {
                              "type": "string",
                              "example": "AF193766EBFDE191B9AB04CD970419ZZ"
                            }
                          }
                        }
                      }
                    },
                    {
                      "title": "Cable Tv",
                      "type": "object",
                      "properties": {
                        "statusCode": {
                          "type": "integer",
                          "example": 200,
                          "default": 0
                        },
                        "message": {
                          "type": "string",
                          "example": "Cable TV number verified successfully."
                        },
                        "sessionId": {
                          "type": "string",
                          "example": "090286251125033820106691997841"
                        },
                        "data": {
                          "type": "object",
                          "properties": {
                            "name": {
                              "type": "string",
                              "example": "John Doe"
                            }
                          }
                        }
                      }
                    }
                  ]
                }
              }
            }
          },
          "400": {
            "description": "400",
            "content": {
              "application/json": {
                "examples": {
                  "Result": {
                    "value": "{}"
                  }
                },
                "schema": {
                  "type": "object",
                  "properties": {}
                }
              }
            }
          }
        },
        "deprecated": false
      }
    }
  },
  "x-readme": {
    "headers": [],
    "explorer-enabled": true,
    "proxy-enabled": true
  },
  "x-readme-fauxas": true,
  "_id": "61e69ed05b7bd4006a962eed:61f292de09fb770049a23bde"
}
```Purchase Airtime

# Purchase Airtime

Call this endpoint to purchase airtime.

# OpenAPI definition

```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "Safe Haven IBS API",
    "version": "1.0"
  },
  "servers": [
    {
      "url": "https://api.sandbox.safehavenmfb.com"
    }
  ],
  "components": {
    "securitySchemes": {
      "sec0": {
        "type": "oauth2",
        "flows": {}
      }
    }
  },
  "security": [
    {
      "sec0": []
    }
  ],
  "paths": {
    "/vas/pay/airtime": {
      "post": {
        "summary": "Purchase Airtime",
        "description": "Call this endpoint to purchase airtime.",
        "operationId": "pay-airtime",
        "parameters": [
          {
            "name": "ClientID",
            "in": "header",
            "description": "This is your 'ibs_client_id' returned in the response when you generate an api token",
            "required": true,
            "schema": {
              "type": "string"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "serviceCategoryId",
                  "amount",
                  "channel",
                  "debitAccountNumber",
                  "phoneNumber"
                ],
                "properties": {
                  "serviceCategoryId": {
                    "type": "string",
                    "description": "The `_id` of the service category."
                  },
                  "amount": {
                    "type": "number",
                    "description": "The amount to purchase.",
                    "default": 0,
                    "format": "float"
                  },
                  "channel": {
                    "type": "string",
                    "description": "The channel the purchase is made on",
                    "default": "WEB",
                    "enum": [
                      "WEB",
                      "POS",
                      "ATM"
                    ]
                  },
                  "debitAccountNumber": {
                    "type": "string",
                    "description": "The account number for the account to be debited."
                  },
                  "phoneNumber": {
                    "type": "string",
                    "description": "The beneficiary phone number."
                  },
                  "statusUrl": {
                    "type": "string",
                    "description": "The status url."
                  }
                }
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "200",
            "content": {
              "application/json": {
                "examples": {
                  "Result": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Airtime has been purchased successfully.\",\n    \"data\": {\n        \"clientId\": \"61e5a83ac6f0ec001ee90fac\",\n        \"serviceCategoryId\": \"61e988830e69308aa37a7a99\",\n        \"reference\": \"7f42c0b611ea4dbbb6adef260b901d69\",\n        \"status\": \"successful\",\n        \"amount\": 10000,\n        \"id\": \"64a53fbe042f713106e0e0d0\"\n    }\n}"
                  }
                },
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "integer",
                      "example": 200,
                      "default": 0
                    },
                    "message": {
                      "type": "string",
                      "example": "Airtime has been purchased successfully."
                    },
                    "data": {
                      "type": "object",
                      "properties": {
                        "clientId": {
                          "type": "string",
                          "example": "61e5a83ac6f0ec001ee90fac"
                        },
                        "serviceCategoryId": {
                          "type": "string",
                          "example": "61e988830e69308aa37a7a99"
                        },
                        "reference": {
                          "type": "string",
                          "example": "7f42c0b611ea4dbbb6adef260b901d69"
                        },
                        "status": {
                          "type": "string",
                          "example": "successful"
                        },
                        "amount": {
                          "type": "integer",
                          "example": 10000,
                          "default": 0
                        },
                        "id": {
                          "type": "string",
                          "example": "64a53fbe042f713106e0e0d0"
                        }
                      }
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "400",
            "content": {
              "application/json": {
                "examples": {
                  "Result": {
                    "value": "{}"
                  }
                },
                "schema": {
                  "type": "object",
                  "properties": {}
                }
              }
            }
          }
        },
        "deprecated": false
      }
    }
  },
  "x-readme": {
    "headers": [],
    "explorer-enabled": true,
    "proxy-enabled": true
  },
  "x-readme-fauxas": true,
  "_id": "61e69ed05b7bd4006a962eed:61f293325b8cc804460cdcf0"
}
```Purchase a Data Bundle

# Purchase a Data Bundle

Call this endpoint to purchase a data bundle.

# OpenAPI definition

```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "Safe Haven IBS API",
    "version": "1.0"
  },
  "servers": [
    {
      "url": "https://api.sandbox.safehavenmfb.com"
    }
  ],
  "components": {
    "securitySchemes": {
      "sec0": {
        "type": "oauth2",
        "flows": {}
      }
    }
  },
  "security": [
    {
      "sec0": []
    }
  ],
  "paths": {
    "/vas/pay/data": {
      "post": {
        "summary": "Purchase a Data Bundle",
        "description": "Call this endpoint to purchase a data bundle.",
        "operationId": "buy-a-data-bundle",
        "parameters": [
          {
            "name": "ClientID",
            "in": "header",
            "description": "This is your 'ibs_client_id' returned in the response when you generate an api token",
            "required": true,
            "schema": {
              "type": "string"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "serviceCategoryId",
                  "bundleCode",
                  "amount",
                  "channel",
                  "debitAccountNumber",
                  "phoneNumber"
                ],
                "properties": {
                  "serviceCategoryId": {
                    "type": "string",
                    "description": "The `_id` of the service category."
                  },
                  "bundleCode": {
                    "type": "string",
                    "description": "The bundleCode of the data bundle."
                  },
                  "amount": {
                    "type": "number",
                    "description": "The cost of the data.",
                    "default": 0,
                    "format": "float"
                  },
                  "channel": {
                    "type": "string",
                    "description": "The channel the purchase is made on",
                    "default": "WEB",
                    "enum": [
                      "WEB",
                      "POS",
                      "ATM"
                    ]
                  },
                  "debitAccountNumber": {
                    "type": "string",
                    "description": "The account number for the account to be debited."
                  },
                  "phoneNumber": {
                    "type": "string",
                    "description": "The beneficiary phone number."
                  },
                  "statusUrl": {
                    "type": "string",
                    "description": "The status url."
                  }
                }
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "200",
            "content": {
              "application/json": {
                "examples": {
                  "Result": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Data Bundle purchased successfully.\",\n    \"data\": {\n        \"clientId\": \"61e5a83ac6f0ec001ee90fac\",\n        \"serviceCategoryId\": \"61e98a4dbce8e444a4976651\",\n        \"reference\": \"d2140c04cdc442b1b411ab2cde3e700f\",\n        \"status\": \"successful\",\n        \"amount\": 20000,\n        \"id\": \"64a5401e042f713106e0e0da\"\n    }\n}"
                  }
                },
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "integer",
                      "example": 200,
                      "default": 0
                    },
                    "message": {
                      "type": "string",
                      "example": "Data Bundle purchased successfully."
                    },
                    "data": {
                      "type": "object",
                      "properties": {
                        "clientId": {
                          "type": "string",
                          "example": "61e5a83ac6f0ec001ee90fac"
                        },
                        "serviceCategoryId": {
                          "type": "string",
                          "example": "61e98a4dbce8e444a4976651"
                        },
                        "reference": {
                          "type": "string",
                          "example": "d2140c04cdc442b1b411ab2cde3e700f"
                        },
                        "status": {
                          "type": "string",
                          "example": "successful"
                        },
                        "amount": {
                          "type": "integer",
                          "example": 20000,
                          "default": 0
                        },
                        "id": {
                          "type": "string",
                          "example": "64a5401e042f713106e0e0da"
                        }
                      }
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "400",
            "content": {
              "application/json": {
                "examples": {
                  "Result": {
                    "value": "{}"
                  }
                },
                "schema": {
                  "type": "object",
                  "properties": {}
                }
              }
            }
          }
        },
        "deprecated": false
      }
    }
  },
  "x-readme": {
    "headers": [],
    "explorer-enabled": true,
    "proxy-enabled": true
  },
  "x-readme-fauxas": true,
  "_id": "61e69ed05b7bd4006a962eed:61f293e93ed8900022a242c4"
}
```Purchase a Cable TV Subscription

# Purchase a Cable TV Subscription

Call this endpoint to purchase a cable tv subscription..

# OpenAPI definition

```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "Safe Haven IBS API",
    "version": "1.0"
  },
  "servers": [
    {
      "url": "https://api.sandbox.safehavenmfb.com"
    }
  ],
  "components": {
    "securitySchemes": {
      "sec0": {
        "type": "oauth2",
        "flows": {}
      }
    }
  },
  "security": [
    {
      "sec0": []
    }
  ],
  "paths": {
    "/vas/pay/cable-tv": {
      "post": {
        "summary": "Purchase a Cable TV Subscription",
        "description": "Call this endpoint to purchase a cable tv subscription..",
        "operationId": "purchase-a-cable-tv-subscription",
        "parameters": [
          {
            "name": "ClientID",
            "in": "header",
            "description": "This is your 'ibs_client_id' returned in the response when you generate an api token",
            "required": true,
            "schema": {
              "type": "string"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "serviceCategoryId",
                  "bundleCode",
                  "amount",
                  "channel",
                  "debitAccountNumber",
                  "cardNumber"
                ],
                "properties": {
                  "serviceCategoryId": {
                    "type": "string",
                    "description": "The `_id` of the service category."
                  },
                  "bundleCode": {
                    "type": "string",
                    "description": "The bundleCode of the data bundle."
                  },
                  "amount": {
                    "type": "number",
                    "description": "The cost of the data.",
                    "default": 0,
                    "format": "float"
                  },
                  "channel": {
                    "type": "string",
                    "description": "The channel the purchase is made on",
                    "default": "WEB",
                    "enum": [
                      "WEB",
                      "POS",
                      "ATM"
                    ]
                  },
                  "debitAccountNumber": {
                    "type": "string",
                    "description": "The account number for the account to be debited."
                  },
                  "cardNumber": {
                    "type": "string",
                    "description": "The card number of the cable tv account."
                  }
                }
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "200",
            "content": {
              "application/json": {
                "examples": {
                  "Result": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Cable TV purchased successfully.\",\n    \"data\": {\n        \"clientId\": \"6874eb47aaad890024be3930\",\n        \"serviceCategoryId\": \"61e98b3562d9b6a917f9302a\",\n        \"reference\": \"c9e316d947914d3db1265dcc8af238fb\",\n        \"status\": \"successful\",\n        \"amount\": 3600,\n        \"id\": \"6925253dc26e15ce5635f618\",\n        \"receiver\": {\n            \"number\": \"8093245829\",\n            \"name\": null,\n            \"customerNumber\": null,\n            \"distribution\": \"DSTV\",\n            \"vendType\": \"\"\n        }\n    }\n}"
                  }
                },
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "integer",
                      "example": 200,
                      "default": 0
                    },
                    "message": {
                      "type": "string",
                      "example": "Cable TV purchased successfully."
                    },
                    "data": {
                      "type": "object",
                      "properties": {
                        "clientId": {
                          "type": "string",
                          "example": "6874eb47aaad890024be3930"
                        },
                        "serviceCategoryId": {
                          "type": "string",
                          "example": "61e98b3562d9b6a917f9302a"
                        },
                        "reference": {
                          "type": "string",
                          "example": "c9e316d947914d3db1265dcc8af238fb"
                        },
                        "status": {
                          "type": "string",
                          "example": "successful"
                        },
                        "amount": {
                          "type": "integer",
                          "example": 3600,
                          "default": 0
                        },
                        "id": {
                          "type": "string",
                          "example": "6925253dc26e15ce5635f618"
                        },
                        "receiver": {
                          "type": "object",
                          "properties": {
                            "number": {
                              "type": "string",
                              "example": "8093245829"
                            },
                            "name": {},
                            "customerNumber": {},
                            "distribution": {
                              "type": "string",
                              "example": "DSTV"
                            },
                            "vendType": {
                              "type": "string",
                              "example": ""
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "400",
            "content": {
              "application/json": {
                "examples": {
                  "Result": {
                    "value": "{}"
                  }
                },
                "schema": {
                  "type": "object",
                  "properties": {}
                }
              }
            }
          }
        },
        "deprecated": false
      }
    }
  },
  "x-readme": {
    "headers": [],
    "explorer-enabled": true,
    "proxy-enabled": true
  },
  "x-readme-fauxas": true,
  "_id": "61e69ed05b7bd4006a962eed:61f294514c646d006a45c8cd"
}
```Pay Utility Bill

# Pay Utility Bill

Call this endpoint to make payment for utility bills.

# OpenAPI definition

```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "Safe Haven IBS API",
    "version": "1.0"
  },
  "servers": [
    {
      "url": "https://api.sandbox.safehavenmfb.com"
    }
  ],
  "components": {
    "securitySchemes": {
      "sec0": {
        "type": "oauth2",
        "flows": {}
      }
    }
  },
  "security": [
    {
      "sec0": []
    }
  ],
  "paths": {
    "/vas/pay/utility": {
      "post": {
        "summary": "Pay Utility Bill",
        "description": "Call this endpoint to make payment for utility bills.",
        "operationId": "pay-utility-bill",
        "parameters": [
          {
            "name": "ClientID",
            "in": "header",
            "description": "This is your 'ibs_client_id' returned in the response when you generate an api token",
            "required": true,
            "schema": {
              "type": "string"
            }
          }
        ],
        "requestBody": {
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "serviceCategoryId",
                  "amount",
                  "channel",
                  "debitAccountNumber",
                  "meterNumber",
                  "vendType"
                ],
                "properties": {
                  "serviceCategoryId": {
                    "type": "string",
                    "description": "The `_id` of the service category."
                  },
                  "amount": {
                    "type": "number",
                    "description": "The cost of the data.",
                    "default": 0,
                    "format": "float"
                  },
                  "channel": {
                    "type": "string",
                    "description": "The channel the purchase is made on",
                    "default": "WEB",
                    "enum": [
                      "WEB",
                      "POS",
                      "ATM"
                    ]
                  },
                  "debitAccountNumber": {
                    "type": "string",
                    "description": "The account number for the account to be debited."
                  },
                  "meterNumber": {
                    "type": "string",
                    "description": "The meter number of the utility account."
                  },
                  "vendType": {
                    "type": "string",
                    "description": "The vendType is returned when you verify meter number"
                  }
                }
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "200",
            "content": {
              "application/json": {
                "examples": {
                  "Result": {
                    "value": "{\n    \"statusCode\": 200,\n    \"message\": \"Utility Package purchased successfully.\",\n    \"data\": {\n        \"clientId\": \"61e5a83ac6f0ec001ee90fac\",\n        \"serviceCategoryId\": \"61e98c4390dbbfc905f48f0d\",\n        \"reference\": \"100bba9fd07949aa9281b07c03c2ca5e\",\n        \"status\": \"successful\",\n        \"amount\": 12342,\n        \"id\": \"64a53e44042f713106e0e093\",\n        \"utilityToken\": \"ssfs-erfedde-srfdf\"\n    }\n}"
                  }
                },
                "schema": {
                  "type": "object",
                  "properties": {
                    "statusCode": {
                      "type": "integer",
                      "example": 200,
                      "default": 0
                    },
                    "message": {
                      "type": "string",
                      "example": "Utility Package purchased successfully."
                    },
                    "data": {
                      "type": "object",
                      "properties": {
                        "clientId": {
                          "type": "string",
                          "example": "61e5a83ac6f0ec001ee90fac"
                        },
                        "serviceCategoryId": {
                          "type": "string",
                          "example": "61e98c4390dbbfc905f48f0d"
                        },
                        "reference": {
                          "type": "string",
                          "example": "100bba9fd07949aa9281b07c03c2ca5e"
                        },
                        "status": {
                          "type": "string",
                          "example": "successful"
                        },
                        "amount": {
                          "type": "integer",
                          "example": 12342,
                          "default": 0
                        },
                        "id": {
                          "type": "string",
                          "example": "64a53e44042f713106e0e093"
                        },
                        "utilityToken": {
                          "type": "string",
                          "example": "ssfs-erfedde-srfdf"
                        }
                      }
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "400",
            "content": {
              "application/json": {
                "examples": {
                  "Result": {
                    "value": "{}"
                  }
                },
                "schema": {
                  "type": "object",
                  "properties": {}
                }
              }
            }
          }
        },
        "deprecated": false
      }
    }
  },
  "x-readme": {
    "headers": [],
    "explorer-enabled": true,
    "proxy-enabled": true
  },
  "x-readme-fauxas": true,
  "_id": "61e69ed05b7bd4006a962eed:61f294bc889a9c00786781b5"
}
```