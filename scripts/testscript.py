import matplotlib.pyplot as plt
import numpy as np

def main():
    # Создаем данные: 100 точек от 0 до 10
    x = np.linspace(0, 10, 100)
    y = np.sin(x)  # Вычисляем синус для каждого значения x

    # Создаем фигуру с заданными размерами
    plt.figure(figsize=(8, 5))

    # Строим график
    plt.plot(x, y, label='sin(x)', color='blue', linewidth=2)

    # Добавляем подписи осей и заголовок
    plt.xlabel('x')
    plt.ylabel('sin(x)')
    plt.title('Простой график синусоиды')

    # Добавляем легенду и сетку
    plt.legend()
    plt.grid(True)

    # Сохраняем график в файл (например, simple_graph.png)
    plt.savefig("simple_graph.png", dpi=300)

    # Отображаем график
    plt.show()

if __name__ == '__main__':
    main()
