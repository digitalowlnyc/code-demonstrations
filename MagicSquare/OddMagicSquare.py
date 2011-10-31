'''
Created on Oct 27, 2011

@author: Bryan Mayor
'''

'''
class OddMagicSquare

Class to create a Magic Square of odd order
'''

class OddMagicSquare():
    order = None
    magic_square = None
    '''
    Creates an n by n magic square, where n is the
    specified order, using the Siamese method
    '''
    def make_magic_square(self):
        
        n = self.order
        
        if(n%2 == 0):
            raise Exception('Cannot create even order Magic Square with this class')
        if(n<=0):
            raise Exception('Order of Magic Square must be positive')
        
        matrix = [ [None]*n for i in range(n) ]
        
        center_column = (n-1)/2
        
        fill_column_index = center_column
        fill_row_index = 0
        
        '''
        Using the Siamese method to fill the square:
        Start filling at center cell of first row
        From there, move up one and right one for new cell
        If the cell is full, move down one of original position instead
        '''
        for i in range(1, pow(n,2)+1):
            
            matrix[fill_row_index][fill_column_index] = i
            
            # Move the referenced cell up one and right one
            new_fill_column_index = (fill_column_index + 1) % n
            new_fill_row_index =  (fill_row_index - 1) % n

            # Check to see if new cell has a value - if it does already
            # then move down one of the original position
            if(matrix[new_fill_row_index][new_fill_column_index] != None):
                new_fill_column_index = fill_column_index
                new_fill_row_index =  (fill_row_index + 1) % n
            
            fill_column_index = new_fill_column_index
            fill_row_index = new_fill_row_index
                        
        self.magic_square = matrix
            
    def printSquare(self, square):
        print("=Magic Square=")
        for row in square:
            print row     
        print("=Magic Square=")
    
    def __init__(self, order):
        if(order%2 == 0):
            raise Exception('Cannot create even order Magic Square with this class')
        if(order<=0):
            raise Exception('Order of Magic Square must be positive')
        self.order = order;
        
# Create a magic square and print it
x = OddMagicSquare(9)
x.make_magic_square()
x.printSquare(x.magic_square)
