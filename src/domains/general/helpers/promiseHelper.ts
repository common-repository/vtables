import type { AxiosError, AxiosResponse } from 'axios';

export const promiseHelper = (promise: any) => {
  return promise.then(
    (response: AxiosResponse) => {
      return { response };
    },
    (error: AxiosError) => {
      return { error };
    }
  );
};
